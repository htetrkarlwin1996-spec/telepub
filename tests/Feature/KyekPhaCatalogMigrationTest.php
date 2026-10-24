<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KyekPhaCatalogMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_creates_kyek_pha_account_and_approved_release(): void
    {
        $user = User::where('email', 'lingkyek@gmail.com')->firstOrFail();

        $this->assertSame('Thant Zaw', $user->name);
        $this->assertSame('artist', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->email_verified_at);

        $artist = $user->artist()->firstOrFail();
        $this->assertSame('Kyek Pha', $artist->artist_name);
        $this->assertEquals(85, $artist->revenue_share_percentage);

        $album = Album::where('artist_id', $artist->id)->where('slug', 'maik-khae-kyek-pha')->firstOrFail();
        $this->assertSame('မိုက်ခဲ', $album->title);
        $this->assertSame('single', $album->release_type);
        $this->assertSame('approved', $album->status);
        $this->assertSame('paid', $album->payment_status);
        $this->assertSame('2026-10-21', $album->release_date->toDateString());
        $this->assertSame(
            ['QT66C2603426', 'QT66C2603427'],
            $album->songs()->orderBy('track_number')->pluck('isrc_code')->all()
        );
        $this->assertSame(['4:43', '4:44'], $album->songs()->orderBy('track_number')->pluck('duration')->all());
        Storage::disk('public')->assertExists('cover_art/kyek-pha/maik-khae.jpg');
    }

    public function test_migration_is_idempotent(): void
    {
        $migration = require database_path('migrations/2026_10_24_000000_import_kyek_pha_account_and_single.php');
        $migration->up();

        $user = User::where('email', 'lingkyek@gmail.com')->firstOrFail();
        $artist = $user->artist()->firstOrFail();
        $this->assertSame(1, User::where('email', 'lingkyek@gmail.com')->count());
        $this->assertSame(1, Album::where('artist_id', $artist->id)->where('slug', 'maik-khae-kyek-pha')->count());
        $this->assertSame(2, $artist->songs()->whereIn('isrc_code', ['QT66C2603426', 'QT66C2603427'])->count());
    }
}
