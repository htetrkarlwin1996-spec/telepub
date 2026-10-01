<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Cache::forget('app_setting.display_currency');
        parent::tearDown();
    }

    public function test_admin_can_change_the_global_display_currency_without_converting_amounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artistUser = User::factory()->create(['role' => 'artist', 'email_verified_at' => now()]);
        Artist::create([
            'user_id' => $artistUser->id,
            'artist_name' => 'Currency Artist',
            'available_balance' => 123.45,
        ]);

        $this->actingAs($admin)->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Global Display Currency')
            ->assertSee('Site Maintenance');

        $this->put(route('admin.settings.currency'), ['display_currency' => 'EUR'])
            ->assertRedirect();

        $this->assertDatabaseHas('app_settings', ['key' => 'display_currency', 'value' => 'EUR']);
        $this->actingAs($artistUser)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('EUR 123.45');
        $this->assertEquals(123.45, Artist::firstOrFail()->available_balance);
    }

    public function test_artist_cannot_open_or_update_admin_settings(): void
    {
        $artist = User::factory()->create(['role' => 'artist']);

        $this->actingAs($artist)->get(route('admin.settings'))->assertForbidden();
        $this->put(route('admin.settings.currency'), ['display_currency' => 'EUR'])->assertForbidden();
        $this->put(route('admin.settings.withdrawal-fee'), ['withdrawal_fee_percentage' => 5])->assertForbidden();
    }

    public function test_admin_controls_the_fee_used_by_new_withdrawals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artistUser = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create([
            'user_id' => $artistUser->id, 'artist_name' => 'Fee Artist', 'available_balance' => 100,
        ]);

        $this->actingAs($admin)->put(route('admin.settings.withdrawal-fee'), [
            'withdrawal_fee_percentage' => 5,
        ])->assertRedirect();
        $this->assertDatabaseHas('app_settings', ['key' => 'withdrawal_fee_percentage', 'value' => '5']);

        $this->actingAs($artistUser)->post(route('artist.withdrawals.store'), [
            'amount' => 40, 'payment_method' => 'paypal', 'payment_details' => 'fee@example.com',
        ])->assertRedirect(route('artist.withdrawals'));

        $this->assertDatabaseHas('withdrawals', [
            'artist_id' => $artist->id, 'amount' => 40, 'fee' => 2, 'total' => 38, 'status' => 'pending',
        ]);
        $this->assertSame(60.0, (float) $artist->fresh()->available_balance);
        $this->assertSame(40.0, (float) $artist->fresh()->pending_balance);
        $this->assertSame('5', AppSetting::where('key', 'withdrawal_fee_percentage')->value('value'));

        $withdrawal = $artist->withdrawals()->firstOrFail();
        $this->actingAs($admin)->post(route('admin.withdrawals.reject', $withdrawal), [
            'admin_notes' => 'Test rejection',
        ])->assertRedirect(route('admin.withdrawals'));
        $this->assertSame(100.0, (float) $artist->fresh()->available_balance);
        $this->assertSame(0.0, (float) $artist->fresh()->pending_balance);
        $this->assertSame('rejected', $withdrawal->fresh()->status);
    }
}
