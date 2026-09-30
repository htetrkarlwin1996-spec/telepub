<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\MusicStore;
use App\Models\ReleasePayment;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReleaseCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_selection_goes_to_checkout_and_offline_approval_submits_release(): void
    {
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
    }

    public function test_audio_upload_accepts_chunks_and_reassembles_the_original_file(): void
    {
        Storage::fake('local');
        Storage::fake('s3');
        config([
            'filesystems.release_audio_disk' => 's3',
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
        Storage::disk('s3')->assertExists($path);
        $this->assertSame($content, Storage::disk('s3')->get($path));
    }
}
