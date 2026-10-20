<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InnofectiveCatalogMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_creates_distributor_account_artists_and_two_singles(): void
    {
        $user = User::where('email', 'innofective@gmail.com')->firstOrFail();
        $this->assertSame('Tin Aung Htun', $user->name);
        $this->assertSame('manager', $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('+959442626510', $user->phone);

        $master = $user->masterAccount()->firstOrFail();
        $this->assertSame('Innofective', $master->name);
        $this->assertEqualsCanonicalizing(['Awesome BoyZ', 'Parallax'], $master->artists()->pluck('artist_name')->all());

        $releases = Album::whereIn('upc_code', ['8447536202805', '8447536257959'])->with('songs')->get()->keyBy('upc_code');
        $this->assertCount(2, $releases);
        $this->assertSame('Min Nae Achit', $releases['8447536202805']->title);
        $this->assertSame('GX8LE2442811', $releases['8447536202805']->songs->first()->isrc_code);
        $this->assertSame('Tekno Journey', $releases['8447536257959']->title);
        $this->assertSame('QZXLZ2532892', $releases['8447536257959']->songs->first()->isrc_code);
        $this->assertTrue($releases->every(fn ($release) => $release->status === 'approved' && $release->payment_status === 'paid'));
        Storage::disk('public')->assertExists('cover_art/innofective/min-nae-achit.jpg');
        Storage::disk('public')->assertExists('cover_art/innofective/tekno-journey.jpg');
    }
}
