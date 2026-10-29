<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Artist;
use App\Models\MasterAccount;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterAccountCatalogDurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_account_can_view_an_imported_release_with_clock_format_duration(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $account = MasterAccount::create([
            'owner_user_id' => $manager->id,
            'name' => 'Catalog Manager',
        ]);
        $artist = Artist::create(['artist_name' => 'Managed Artist']);
        $account->artists()->attach($artist->id, [
            'access_level' => 'full_access',
            'status' => 'active',
            'created_by' => $manager->id,
        ]);
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => 'Imported Release',
            'release_type' => 'single',
            'genre' => 'Pop',
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        Song::create([
            'album_id' => $album->id,
            'artist_id' => $artist->id,
            'title' => 'Min Nae Achit',
            'track_number' => 1,
            'duration' => '4:27',
            'status' => 'approved',
        ]);

        $this->actingAs($manager)
            ->withSession(['managed_artist_id' => $artist->id])
            ->get(route('artist.catalog.show', $album))
            ->assertOk()
            ->assertSeeText('Min Nae Achit')
            ->assertSeeText('4:27');
    }

    public function test_duration_formatter_supports_seconds_and_rejects_invalid_values(): void
    {
        $this->assertSame('04:27', formatDuration(267));
        $this->assertSame('4:27', formatDuration('4:27'));
        $this->assertSame('01:02:03', formatDuration(3723));
        $this->assertNull(formatDuration('not-a-duration'));
    }
}
