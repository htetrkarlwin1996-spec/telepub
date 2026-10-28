<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\ReleasePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_filter_user_release_transactions(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $user = User::factory()->create([
            'name' => 'Paying User',
            'email' => 'payer@example.com',
            'role' => 'artist',
            'is_active' => true,
        ]);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Payment Artist']);
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'Paid Release',
            'release_type' => 'single',
            'status' => 'submitted',
            'payment_status' => 'paid',
        ]);
        ReleasePayment::create([
            'album_id' => $album->id,
            'user_id' => $user->id,
            'provider' => 'stripe',
            'status' => 'paid',
            'amount' => 19.99,
            'currency' => 'USD',
            'reference' => 'REL-STRIPE-PAID-1',
            'addon_services' => ['global_performance'],
            'paid_at' => now(),
        ]);
        ReleasePayment::create([
            'album_id' => $album->id,
            'user_id' => $user->id,
            'provider' => 'paypal',
            'status' => 'cancelled',
            'amount' => 19.99,
            'currency' => 'USD',
            'reference' => 'REL-PAYPAL-CANCELLED-1',
            'addon_services' => [],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.transactions'))
            ->assertOk()
            ->assertSeeText('Transactions')
            ->assertSeeText('Paying User')
            ->assertSeeText('payer@example.com')
            ->assertSeeText('Paid Release')
            ->assertSeeText('USD 19.99')
            ->assertSeeText('Global Performance Royalties')
            ->assertSeeText('REL-STRIPE-PAID-1')
            ->assertSeeText('REL-PAYPAL-CANCELLED-1');

        $this->get(route('admin.transactions', ['status' => 'paid', 'provider' => 'stripe']))
            ->assertOk()
            ->assertSeeText('REL-STRIPE-PAID-1')
            ->assertDontSeeText('REL-PAYPAL-CANCELLED-1');

        $this->get(route('admin.transactions', ['search' => 'Payment Artist']))
            ->assertOk()
            ->assertSeeText('REL-STRIPE-PAID-1');
    }

    public function test_artist_cannot_view_admin_transactions(): void
    {
        $artist = User::factory()->create(['role' => 'artist', 'is_active' => true]);

        $this->actingAs($artist)->get(route('admin.transactions'))->assertForbidden();
    }
}
