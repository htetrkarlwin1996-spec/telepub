<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MrLuffieCatalogMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_creates_mr_luffie_account_and_ppf_ep(): void
    {
        $user = User::where('email', 'sheinwinthtet6@gmail.com')->firstOrFail();

        $this->assertSame('Mr.Luffie', $user->name);
        $this->assertSame('artist', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('$2y$10$SAi4CxMjaBzMzuX.HUJWvOOlmn7N1V84gVV8iX2jH1iOCkyyCQD0u', $user->getRawOriginal('password'));

        $artist = $user->artist()->firstOrFail();
        $this->assertSame('Mr.Luffie', $artist->artist_name);
        $this->assertEquals(85, $artist->revenue_share_percentage);

        $album = Album::where('artist_id', $artist->id)->where('upc_code', '8447721188129')->firstOrFail();
        $this->assertSame('P.P.F', $album->title);
        $this->assertSame('ep', $album->release_type);
        $this->assertSame('approved', $album->status);
        $this->assertSame('paid', $album->payment_status);
        $this->assertCount(5, $album->songs);
        $this->assertSame(
            ['QZNW72615042', 'QZNW72615043', 'QZNW72615044', 'QZNW72615045', 'QZNW72615046'],
            $album->songs()->orderBy('track_number')->pluck('isrc_code')->all()
        );
        $this->assertTrue($album->songs->every(fn ($song) => $song->explicit && $song->status === 'approved'));
        Storage::disk('public')->assertExists('cover_art/mr-luffie/ppf.jpg');
    }
}
