<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const EMAIL = 'wintyeeshunn2@gmail.com';

    public function up(): void
    {
        $artist = DB::table('users')
            ->join('artists', 'artists.user_id', '=', 'users.id')
            ->where('users.email', self::EMAIL)
            ->select('artists.*')
            ->first();

        if (! $artist) {
            return;
        }

        foreach ($this->releases() as $release) {
            $source = database_path('data/wyne_lay_covers/'.$release['cover']);
            if (! is_file($source)) {
                throw new RuntimeException('Wyne Lay cover asset is missing: '.$source);
            }
            Storage::disk('public')->put('cover_art/wyne-lay/'.$release['cover'], file_get_contents($source));
        }

        DB::transaction(function () use ($artist) {
            foreach ($this->releases() as $release) {
                $this->upsertRelease($artist, $release);
            }
        });
    }

    private function upsertRelease(object $artist, array $release): void
    {
        $now = now();
        $albumValues = [
            'title' => $release['title'],
            'slug' => Str::slug($release['title']).'-wyne-lay',
            'release_type' => 'single',
            'cover_art' => 'cover_art/wyne-lay/'.$release['cover'],
            'genre' => 'Pop',
            'label' => 'TeleMusic',
            'release_date' => $release['release_date'],
            'price' => 0,
            'selected_store_ids' => json_encode([]),
            'selected_addons' => json_encode([]),
            'payment_status' => 'paid',
            'upc_code' => $release['upc'],
            'status' => 'approved',
            'copyright_holder' => $release['copyright'],
            'phonogram_right_holder' => $release['phonogram'],
            'request_new_isrc' => false,
            'approved_at' => $release['approved_at'].' 00:00:00',
            'notes' => 'Imported approved SonoSuite release. Reference: '.$release['reference'].'. Source album ID: '.$release['source_id'].'. '.$release['notes'],
            'updated_at' => $now,
        ];

        $album = DB::table('albums')
            ->where('artist_id', $artist->id)
            ->where('upc_code', $release['upc'])
            ->first();

        if ($album) {
            DB::table('albums')->where('id', $album->id)->update($albumValues);
            $albumId = $album->id;
        } else {
            $albumId = DB::table('albums')->insertGetId($albumValues + [
                'artist_id' => $artist->id,
                'created_at' => $now,
            ]);
        }

        $songValues = [
            'album_id' => $albumId,
            'artist_id' => $artist->id,
            'title' => $release['track_title'],
            'version' => $release['version'],
            'track_number' => 1,
            'duration' => $release['duration'],
            'isrc_code' => $release['isrc'],
            'request_new_isrc' => false,
            'explicit' => false,
            'genre' => 'Pop',
            'language' => 'Burmese',
            'primary_artists' => json_encode(['Wyne Lay'], JSON_UNESCAPED_UNICODE),
            'composers' => json_encode($release['composers'], JSON_UNESCAPED_UNICODE),
            'lyricist' => json_encode($release['lyricists'], JSON_UNESCAPED_UNICODE),
            'producers' => json_encode($release['producers'], JSON_UNESCAPED_UNICODE),
            'vocals' => json_encode(['Wyne Lay'], JSON_UNESCAPED_UNICODE),
            'featuring' => json_encode([], JSON_UNESCAPED_UNICODE),
            'status' => 'approved',
            'updated_at' => $now,
        ];

        $song = DB::table('songs')->where('isrc_code', $release['isrc'])->first();
        if ($song) {
            DB::table('songs')->where('id', $song->id)->update($songValues);
        } else {
            DB::table('songs')->insert($songValues + ['created_at' => $now]);
        }
    }

    private function releases(): array
    {
        return [
            [
                'title' => 'Yay Tway Pat', 'track_title' => 'Yay Tway Pat', 'version' => null,
                'upc' => '8447352698042', 'isrc' => 'QZXLZ2508490', 'duration' => '3:54',
                'release_date' => '2025-03-12', 'approved_at' => '2025-03-06',
                'copyright' => '2025 Wyne Lay', 'phonogram' => '2025 Wyne Lay',
                'composers' => ['Wyne Lay'], 'lyricists' => ['Wyne Lay'], 'producers' => ['Wyne Lay'],
                'reference' => 'TELEMUSIC2500023', 'source_id' => '1917134', 'cover' => 'yay-tway-pat.jpg',
                'notes' => 'Mixing engineer: Aung Lay.',
            ],
            [
                'title' => 'Htar Khae Mar Lar (TBoy Remix)', 'track_title' => 'Htar Khae Mar Lar', 'version' => 'TBoy Remix',
                'upc' => '8447352940165', 'isrc' => 'QZXLZ2518966', 'duration' => '3:27',
                'release_date' => '2025-05-08', 'approved_at' => '2025-05-15',
                'copyright' => '2025 Wyne Lay', 'phonogram' => '2025 TeleMusic, LLC',
                'composers' => ['D Htet'], 'lyricists' => ['Wyne Lay'], 'producers' => ['TBoy (Bone+)'],
                'reference' => 'TELEMUSIC2500043', 'source_id' => '2006907', 'cover' => 'htar-khae-mar-lar-tboy-remix.jpg',
                'notes' => 'Remixer: TBoy (Bone+).',
            ],
            [
                'title' => 'Snow City', 'track_title' => 'Snow City', 'version' => null,
                'upc' => '8447536770205', 'isrc' => 'QZXLZ2558797', 'duration' => '4:14',
                'release_date' => '2025-12-03', 'approved_at' => '2025-12-02',
                'copyright' => '2025 TeleMusic, LLC', 'phonogram' => '2025 TeleMusic, LLC',
                'composers' => ['Phoe Kar'], 'lyricists' => ['Phoe Kar'], 'producers' => ['Wyne Lay'],
                'reference' => 'TELEMUSIC2500115', 'source_id' => '2277533', 'cover' => 'snow-city.jpg',
                'notes' => 'Mixing engineer: Ko Shan Lay.',
            ],
            [
                'title' => 'Yay Tway Pat (New Version)', 'track_title' => 'Yay Tway Pat', 'version' => 'New Version',
                'upc' => '8447721168626', 'isrc' => 'QZNW72614436', 'duration' => '4:07',
                'release_date' => '2026-03-13', 'approved_at' => '2026-03-10',
                'copyright' => '2026 Wyne Lay', 'phonogram' => '2026 Wyne Lay',
                'composers' => ['Wyne Lay'], 'lyricists' => ['Wyne Lay'],
                'producers' => ['Fireworks Production', 'Lokapala Production'],
                'reference' => 'TELEMUSIC2600131', 'source_id' => '2442581', 'cover' => 'yay-tway-pat-new-version.jpg',
                'notes' => 'Mixing engineer: Chan Aye Win.',
            ],
        ];
    }

    public function down(): void
    {
        $artistId = DB::table('users')
            ->join('artists', 'artists.user_id', '=', 'users.id')
            ->where('users.email', self::EMAIL)
            ->value('artists.id');

        if ($artistId) {
            DB::table('albums')->where('artist_id', $artistId)
                ->whereIn('upc_code', array_column($this->releases(), 'upc'))
                ->delete();
        }

        foreach ($this->releases() as $release) {
            Storage::disk('public')->delete('cover_art/wyne-lay/'.$release['cover']);
        }
    }
};
