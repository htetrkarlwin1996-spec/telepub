<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\ReleasePayment;
use App\Models\Song;
use App\Models\User;
use App\Notifications\ReleaseSubmitted;
use App\Notifications\UserActivityNotification;
use App\Services\MyanMyanPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReleaseCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_selection_goes_to_checkout_and_offline_approval_submits_release(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Checkout Artist']);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'Paid Single', 'release_type' => 'single', 'status' => 'draft']);
        $song = Song::create(['album_id' => $album->id, 'artist_id' => $artist->id, 'title' => 'Paid Song', 'track_number' => 1, 'status' => 'draft']);
        $store = MusicStore::create(['name' => 'Checkout Store', 'slug' => 'checkout-store']);

        $this->actingAs($user)->post(route('artist.catalog.store-step4', $album), ['stores' => [$store->id]])
            ->assertRedirect(route('artist.catalog.checkout', $album));
        $this->assertDatabaseCount('distributions', 0);
        $this->assertSame([$store->id], $album->fresh()->selected_store_ids);

        $this->get(route('artist.catalog.checkout', $album))->assertOk()->assertSee('USD 14.99')->assertSee('MMQR');
        $this->post(route('artist.catalog.pay', $album), ['method' => 'offline'])->assertSessionHas('success');
        $payment = ReleasePayment::firstOrFail();
        $this->assertSame('THB', $payment->currency);
        $this->assertEquals(539.64, $payment->amount);
        $this->assertSame('pending', $album->fresh()->payment_status);

        $this->actingAs($admin)->post(route('admin.release-payments.approve', $payment))->assertSessionHas('success');
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('submitted', $album->fresh()->status);
        $this->assertSame('submitted', $song->fresh()->status);
        $this->assertDatabaseHas('distributions', ['album_id' => $album->id, 'song_id' => $song->id, 'store_id' => $store->id, 'status' => 'submitted']);
        Notification::assertSentTo($admin, ReleaseSubmitted::class);
        Notification::assertSentOnDemand(ReleaseSubmitted::class);
        Notification::assertSentTo($user, UserActivityNotification::class, fn (UserActivityNotification $notification) => $notification->toMail($user)->subject === '[TeleMusic] Release submitted');
    }

    public function test_audio_upload_accepts_chunks_and_reassembles_the_original_file(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        config([
            'filesystems.release_audio_disk' => 'public',
            'filesystems.release_audio_prefix' => 'dashboardtelemusic',
        ]);
        $user = User::factory()->create(['role' => 'artist']);
        Artist::create(['user_id' => $user->id, 'artist_name' => 'Upload Artist']);
        $content = 'first-audio-partsecond-audio-part';
        $uploadId = '12345678-1234-1234-1234-123456789012';

        $common = ['upload_id' => $uploadId, 'total_chunks' => 2, 'original_name' => 'master.wav', 'total_size' => strlen($content)];
        $this->actingAs($user)->post(route('artist.catalog.upload-audio'), $common + ['chunk_index' => 0, 'audio_file' => UploadedFile::fake()->createWithContent('master.wav.part', 'first-audio-part')])
            ->assertOk()->assertJson(['success' => true, 'complete' => false]);
        $response = $this->post(route('artist.catalog.upload-audio'), $common + ['chunk_index' => 1, 'audio_file' => UploadedFile::fake()->createWithContent('master.wav.part', 'second-audio-part')])
            ->assertOk()->assertJson(['success' => true, 'complete' => true]);

        $path = $response->json('path');
        $this->assertStringStartsWith('dashboardtelemusic/tracks/', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame($content, Storage::disk('public')->get($path));
    }

    public function test_pending_myanmyanpay_qr_opens_in_a_popup(): void
    {
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'QR Artist']);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'QR Single', 'release_type' => 'single', 'status' => 'draft']);
        ReleasePayment::create([
            'album_id' => $album->id,
            'user_id' => $user->id,
            'provider' => 'myanmyanpay',
            'amount' => 8955,
            'currency' => 'MMK',
            'reference' => 'REL-MYA-POPUP',
            'qr_data' => 'test-emvco-mmqr-payload',
        ]);

        $this->actingAs($user)
            ->get(route('artist.catalog.checkout', $album))
            ->assertOk()
            ->assertSee('id="mmqr-modal"', false)
            ->assertSee('hidden items-center', false)
            ->assertSee('Scan MMQR')
            ->assertSee('MMQR Payment Pending')
            ->assertSee('Select payment method')
            ->assertSee('x-show="method"', false)
            ->assertSeeText('Cancel Transaction')
            ->assertSeeText('Download QR')
            ->assertSeeText('Payment powered by MyanMyanPay.')
            ->assertSeeText('Check Status')
            ->assertSee('data-mmqr-timer', false)
            ->assertSee('mmqr-logo.svg', false)
            ->assertSee('REL-MYA-POPUP');
    }

    public function test_active_myanmyanpay_order_is_reused_instead_of_creating_another_order(): void
    {
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Cached QR Artist']);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'Cached QR Single', 'release_type' => 'single', 'status' => 'draft']);
        $payment = ReleasePayment::create([
            'album_id' => $album->id, 'user_id' => $user->id, 'provider' => 'myanmyanpay',
            'amount' => 8955, 'currency' => 'MMK', 'reference' => 'REL-MYA-CACHED',
            'qr_data' => 'cached-emvco-mmqr-payload', 'expires_at' => now()->addMinutes(15),
        ]);
        $this->app->instance(MyanMyanPayService::class, new class extends MyanMyanPayService
        {
            public function configured(): bool
            {
                return true;
            }

            public function pay(array $payload): array
            {
                throw new \RuntimeException('A new gateway order must not be requested.');
            }
        });

        $this->actingAs($user)->post(route('artist.catalog.pay', $album), ['method' => 'myanmyanpay'])
            ->assertRedirect(route('artist.catalog.checkout', $album))
            ->assertSessionHas('success')
            ->assertSessionHas('open_mmqr', true);

        $this->assertDatabaseCount('release_payments', 1);
        $this->assertSame('REL-MYA-CACHED', $payment->fresh()->reference);
    }

    public function test_myanmyanpay_status_poll_marks_a_successful_payment_paid(): void
    {
        Notification::fake();
        User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Polling Artist']);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'Paid QR Single', 'release_type' => 'single', 'status' => 'draft']);
        $payment = ReleasePayment::create([
            'album_id' => $album->id, 'user_id' => $user->id, 'provider' => 'myanmyanpay',
            'amount' => 8955, 'currency' => 'MMK', 'reference' => 'REL-MYA-PAID',
            'qr_data' => 'paid-emvco-mmqr-payload', 'expires_at' => now()->addMinutes(15),
        ]);
        $this->app->instance(MyanMyanPayService::class, new class extends MyanMyanPayService
        {
            public function configured(): bool
            {
                return true;
            }

            public function get(array $payload): array
            {
                return [
                    'orderId' => $payload['orderId'], 'status' => 'SUCCESS', 'condition' => 'TOUCHED',
                    'amount' => 8955, 'currency' => 'MMK', 'transactionRefId' => 'MMQR-PAID-1',
                ];
            }
        });

        $this->actingAs($user)->getJson(route('artist.release-payments.status', $payment))
            ->assertOk()->assertJson(['status' => 'paid', 'completed' => true]);

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertSame('paid', $album->fresh()->payment_status);
        $this->assertSame('submitted', $album->fresh()->status);
    }

    public function test_myanmyanpay_webhook_accepts_documented_success_conditions(): void
    {
        Notification::fake();
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Webhook Artist']);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'Webhook Single', 'release_type' => 'single', 'status' => 'draft']);
        $payment = ReleasePayment::create([
            'album_id' => $album->id, 'user_id' => $user->id, 'provider' => 'myanmyanpay',
            'amount' => 8955, 'currency' => 'MMK', 'reference' => 'REL-MYA-WEBHOOK',
            'qr_data' => 'webhook-emvco-mmqr-payload', 'expires_at' => now()->addMinutes(15),
        ]);
        $this->app->instance(MyanMyanPayService::class, new class extends MyanMyanPayService
        {
            public function verify(string $payload, string $nonce, string $signature): bool
            {
                return true;
            }
        });

        $this->postJson(route('webhooks.myanmyanpay'), [
            'orderId' => $payment->reference, 'status' => 'SUCCESS', 'condition' => 'PRISTINE',
            'amount' => 8955, 'currency' => 'MMK', 'transactionRefId' => 'MMQR-WEBHOOK-1',
        ], ['X-Mmpay-Nonce' => 'nonce', 'X-Mmpay-Signature' => 'signature'])
            ->assertOk()->assertJson(['received' => true]);

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('submitted', $album->fresh()->status);
    }

    public function test_artist_can_cancel_a_pending_myanmyanpay_transaction(): void
    {
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Cancel QR Artist']);
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'Cancel QR Single',
            'release_type' => 'single',
            'status' => 'draft',
            'payment_status' => 'pending',
        ]);
        $payment = ReleasePayment::create([
            'album_id' => $album->id,
            'user_id' => $user->id,
            'provider' => 'myanmyanpay',
            'amount' => 8955,
            'currency' => 'MMK',
            'reference' => 'REL-MYA-CANCEL',
            'qr_data' => 'test-emvco-mmqr-payload',
        ]);

        $this->app->instance(MyanMyanPayService::class, new class extends MyanMyanPayService
        {
            public function configured(): bool
            {
                return true;
            }

            public function cancel(array $payload): array
            {
                return ['orderId' => $payload['orderId'], 'status' => 'CANCELLED'];
            }
        });

        $this->actingAs($user)
            ->post(route('artist.release-payments.cancel', $payment))
            ->assertRedirect(route('artist.catalog.checkout', $album))
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->qr_data);
        $this->assertSame('unpaid', $album->fresh()->payment_status);

        $this->get(route('artist.catalog.checkout', $album))
            ->assertOk()
            ->assertDontSee('id="mmqr-modal"', false)
            ->assertDontSee('MMQR Payment Pending');
    }

    public function test_missing_myanmyanpay_order_clears_the_local_pending_transaction(): void
    {
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Missing QR Artist']);
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'Missing QR Single',
            'release_type' => 'single',
            'status' => 'draft',
            'payment_status' => 'pending',
        ]);
        $payment = ReleasePayment::create([
            'album_id' => $album->id,
            'user_id' => $user->id,
            'provider' => 'myanmyanpay',
            'amount' => 8955,
            'currency' => 'MMK',
            'reference' => 'REL-MYA-MISSING',
            'qr_data' => 'missing-emvco-mmqr-payload',
        ]);

        $this->app->instance(MyanMyanPayService::class, new class extends MyanMyanPayService
        {
            public function configured(): bool
            {
                return true;
            }

            public function cancel(array $payload): array
            {
                throw new \Exception('404 Not Found: {"success":false,"message":"Order Not Found"}');
            }
        });

        $this->actingAs($user)
            ->post(route('artist.release-payments.cancel', $payment))
            ->assertRedirect(route('artist.catalog.checkout', $album))
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->qr_data);
        $this->assertSame('unpaid', $album->fresh()->payment_status);
    }
}
