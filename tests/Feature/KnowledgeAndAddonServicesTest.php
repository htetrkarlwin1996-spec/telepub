<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\AppSetting;
use App\Models\Artist;
use App\Models\KnowledgePost;
use App\Models\MusicStore;
use App\Models\ReleasePayment;
use App\Models\Royalty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KnowledgeAndAddonServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_can_read_seeded_knowledge_posts(): void
    {
        $user = User::factory()->create(['role' => 'artist']);
        Artist::create(['user_id' => $user->id, 'artist_name' => 'Knowledge Artist']);

        $this->actingAs($user)->get(route('knowledge.index'))
            ->assertOk()->assertSeeText('Music Rights & Royalties Guide')->assertSeeText('Global Performance Royalties');

        $post = KnowledgePost::where('slug', 'mechanical-royalties-streaming-downloads')->firstOrFail();
        $this->get(route('knowledge.show', $post))->assertOk()->assertSee('Mechanical Licensing Societies');
    }

    public function test_admin_can_create_and_publish_a_knowledge_post(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.knowledge.store'), [
            'title' => 'New Rights Guide', 'excerpt' => 'A useful guide for artists.',
            'body' => 'Complete article body.', 'sort_order' => 10, 'is_published' => 1,
        ])->assertRedirect(route('admin.knowledge.index'));

        $this->assertDatabaseHas('knowledge_posts', ['slug' => 'new-rights-guide', 'is_published' => true]);
    }

    public function test_admin_controls_addon_prices_and_checkout_charges_selected_services(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.settings.release-pricing'), [
            'release_price_first_usd' => 10, 'release_price_per_track_usd' => 10,
            'release_price_over_8_tracks_usd' => 30,
            'addon_composer_songwriter_usd' => 5, 'addon_global_performance_usd' => 7,
            'addon_mechanical_usd' => 3, 'usd_to_thb_rate' => 36, 'usd_to_mmk_rate' => 4500,
            'offline_bank_instructions' => 'Transfer instructions',
        ])->assertSessionHas('success');
        $this->assertSame('7', AppSetting::where('key', 'addon_global_performance_usd')->value('value'));

        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Addon Artist']);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'Addon Album', 'release_type' => 'album', 'status' => 'draft']);
        $store = MusicStore::create(['name' => 'Addon Store', 'slug' => 'addon-store', 'is_active' => true]);

        $this->actingAs($user)->get(route('artist.catalog.step4', $album))
            ->assertOk()
            ->assertSeeText('Royalty Collection Add-on Services')
            ->assertSeeText('Composer / Songwriter Royalties')
            ->assertSeeText('Global Performance Royalties')
            ->assertSeeText('Mechanical Royalties')
            ->assertSee(route('knowledge.show', KnowledgePost::where('slug', 'mechanical-royalties-streaming-downloads')->firstOrFail()));

        $this->post(route('artist.catalog.store-step4', $album), [
            'stores' => [$store->id], 'addons' => ['composer_songwriter', 'mechanical'],
        ])->assertRedirect(route('artist.catalog.checkout', $album));
        $this->post(route('artist.catalog.pay', $album), ['method' => 'offline'])->assertSessionHas('success');

        $payment = ReleasePayment::firstOrFail();
        $this->assertEquals(648, (float) $payment->amount);
        $this->assertSame(['composer_songwriter', 'mechanical'], $payment->addon_services);
        $this->assertSame(['composer_songwriter', 'mechanical'], $album->fresh()->selected_addons);
    }

    public function test_step_two_requires_composer_lyricist_producer_and_vocals(): void
    {
        $user = User::factory()->create(['role' => 'artist']);
        $artist = Artist::create(['user_id' => $user->id, 'artist_name' => 'Credit Artist']);
        $album = Album::create(['artist_id' => $artist->id, 'title' => 'Credits', 'release_type' => 'single', 'status' => 'draft']);

        $this->actingAs($user)->from(route('artist.catalog.step2', $album))->post(route('artist.catalog.store-step2', $album), [
            'tracks' => [[
                'title' => 'Incomplete Credits',
                'primary_artists' => [['name' => 'Credit Artist']],
                'composers' => [['name' => 'A Composer']],
            ]],
        ])->assertRedirect(route('artist.catalog.step2', $album))
            ->assertSessionHasErrors(['tracks.0.lyricist', 'tracks.0.producers', 'tracks.0.vocals'])
            ->assertSessionDoesntHaveErrors(['tracks.0.lyrics']);
    }

    public function test_artist_can_upload_cover_art_with_progress_endpoint_then_create_release(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'artist']);
        Artist::create(['user_id' => $user->id, 'artist_name' => 'Cover Artist']);

        $upload = $this->actingAs($user)->post(route('artist.catalog.upload-cover-art'), [
            'cover_art' => UploadedFile::fake()->image('album-cover.jpg', 3000, 3000),
        ])->assertOk()->assertJson(['success' => true]);
        $path = $upload->json('path');
        Storage::disk('public')->assertExists($path);

        $this->post(route('artist.catalog.store-step1'), [
            'title' => 'Progress Cover Release', 'release_type' => 'single', 'genre' => 'Pop',
            'release_date' => now()->addMonth()->toDateString(), 'cover_art_path' => $path,
            'copyright_holder' => 'Cover Artist', 'phonogram_right_holder' => 'Cover Artist',
        ])->assertRedirect();

        $this->assertDatabaseHas('albums', ['title' => 'Progress Cover Release', 'cover_art' => $path]);
    }

    public function test_new_royalty_labels_are_used_everywhere(): void
    {
        $this->assertSame('Global Performance Royalties', Royalty::TYPES['publishing_rights']);
        $this->assertSame('Composer / Songwriter Royalties', Royalty::TYPES['composer_rights']);
    }
}
