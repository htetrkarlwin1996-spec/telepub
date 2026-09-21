<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const EMAIL = 'shamwela2023@gmail.com';

    public function up(): void
    {
        $artist = DB::table('users')->join('artists', 'artists.user_id', '=', 'users.id')
            ->where('users.email', self::EMAIL)->select('artists.id')->first();
        if (! $artist) {
            return;
        }

        foreach ($this->releases() as $release) {
            $existing = DB::table('albums')->where('artist_id', $artist->id)->where('upc_code', $release['upc'])->first();
            $cover = $existing?->cover_art ?: $this->fetchArtwork($release);
            DB::transaction(function () use ($artist, $release, $existing, $cover) {
                $values = [
                    'title' => $release['title'],
                    'slug' => Str::slug($release['title']).'-'.$release['source'],
                    'release_type' => $release['type'],
                    'cover_art' => $cover,
                    'genre' => $release['genre'],
                    'label' => $release['label'],
                    'release_date' => $release['date'],
                    'upc_code' => $release['upc'],
                    'status' => 'approved',
                    'copyright_holder' => substr($release['date'], 0, 4).' El-Shaddai Music Production',
                    'phonogram_right_holder' => substr($release['date'], 0, 4).' El-Shaddai Music Production',
                    'request_new_isrc' => false,
                    'approved_at' => $release['approved'].' 00:00:00',
                    'notes' => 'SonoSuite source album '.$release['source'].'; reference '.$release['reference'].'.',
                    'updated_at' => now(),
                ];
                if ($existing) {
                    DB::table('albums')->where('id', $existing->id)->update($values);
                    $albumId = $existing->id;
                } else {
                    $albumId = DB::table('albums')->insertGetId($values + ['artist_id' => $artist->id, 'created_at' => now()]);
                }

                foreach ($release['tracks'] as $index => $track) {
                    [$title, $isrc, $duration] = $track;
                    $songValues = [
                        'album_id' => $albumId,
                        'artist_id' => $artist->id,
                        'title' => $title,
                        'track_number' => $index + 1,
                        'duration' => $duration,
                        'isrc_code' => $isrc,
                        'request_new_isrc' => false,
                        'explicit' => false,
                        'genre' => $release['genre'],
                        'primary_artists' => json_encode([$release['performer']], JSON_UNESCAPED_UNICODE),
                        'composers' => json_encode([$release['composer']], JSON_UNESCAPED_UNICODE),
                        'lyricist' => json_encode([$release['lyricist']], JSON_UNESCAPED_UNICODE),
                        'producers' => json_encode([$release['producer']], JSON_UNESCAPED_UNICODE),
                        'vocals' => json_encode([$release['performer']], JSON_UNESCAPED_UNICODE),
                        'status' => 'approved',
                        'updated_at' => now(),
                    ];
                    $song = DB::table('songs')->where('album_id', $albumId)->where('isrc_code', $isrc)->first();
                    if ($song) {
                        DB::table('songs')->where('id', $song->id)->update($songValues);
                    } else {
                        DB::table('songs')->insert($songValues + ['created_at' => now()]);
                    }
                }
            });
        }
    }

    private function fetchArtwork(array $release): ?string
    {
        // Fetch published cover art, never crop a screenshot or substitute a loosely matched image.
        try {
            $lookup = Http::timeout(8)->acceptJson()->get('https://itunes.apple.com/lookup', ['upc' => $release['upc'], 'entity' => 'album']);
            $results = $lookup->successful() ? $lookup->json('results', []) : [];
            if (empty($results)) {
                $search = Http::timeout(8)->acceptJson()->get('https://itunes.apple.com/search', [
                    'term' => $release['title'].' '.$release['performer'],
                    'entity' => 'album',
                    'limit' => 20,
                ]);
                $results = $search->successful() ? $search->json('results', []) : [];
            }
            $normalize = fn (string $value) => preg_replace('/[^\pL\pN]/u', '', mb_strtolower(preg_replace('/\s*[-–]\s*(Single|EP|Album)$/i', '', $value)));
            $result = collect($results)->first(function ($item) use ($release, $normalize) {
                return $normalize((string) ($item['collectionName'] ?? '')) === $normalize($release['title'])
                    && $normalize((string) ($item['artistName'] ?? '')) === $normalize($release['performer']);
            });
            if (! $result || empty($result['artworkUrl100'])) {
                return null;
            }
            $url = preg_replace('/100x100bb/', '1200x1200bb', $result['artworkUrl100']);
            $image = Http::timeout(10)->get($url);
            if (! $image->successful() || ! str_starts_with($image->header('Content-Type', ''), 'image/')) {
                return null;
            }
            $path = 'cover_art/shamwela-'.$release['source'].'.jpg';
            Storage::disk('public')->put($path, $image->body());

            return $path;
        } catch (Throwable) {
            return null;
        }
    }

    private function releases(): array
    {
        $make = fn ($source, $reference, $title, $performer, $type, $date, $approved, $upc, $genre, $composer, $producer, $tracks, $label = 'TeleMusic', $lyricist = null) => compact('source', 'reference', 'title', 'performer', 'type', 'date', 'approved', 'upc', 'genre', 'composer', 'producer', 'tracks', 'label') + ['lyricist' => $lyricist ?? $composer];

        return [
            $make('1926424', 'TELEMUSIC2500025', 'The Promise', 'Lian Bawi', 'single', '2025-03-19', '2025-03-14', '844735272322', 'Spiritual/Gospel', 'Lian Bawi', 'Sha Mwe LA', [['The Promise', 'QZXLZ2510079', '4:37']]),
            $make('1931111', 'TELEMUSIC2500026', 'Hong Sang In', 'Michael Hangsawk', 'single', '2025-03-21', '2025-03-19', '8447352748501', 'Spiritual/Gospel', 'Michael Hangsawk', 'Sha Mwe LA', [['Hong Sang In', 'QZXLZ2510929', '4:04']]),
            $make('2050338', 'TELEMUSIC2500045', 'NUN TAK NA', 'Maa Kim', 'ep', '2025-06-20', '2025-06-12', '8447536072972', 'Spiritual/Gospel', 'John Mung', 'Sha Mwe LA', [['NUN TAK NA', 'QZXLZ2524477', '4:22'], ['NUN TAK NA (Instrumental)', 'QZXLZ2524478', '4:22']]),
            $make('2085000', 'TELEMUSIC2500064', 'Tu Phei Khua Pui Te', 'Pius Sang', 'single', '2025-07-10', '2025-07-15', '0198662695938', 'Alternative/Rock', 'Pius Sang', 'Sha Mwe LA', [['Tu Phei Khua Pui Te', 'QZTB72414925', '4:28']]),
            $make('2110216', 'TELEMUSIC2500067', 'ZOMI KA HIH ANGTANG ING', 'Idol Mungpi', 'single', '2025-07-26', '2025-07-29', '8447536257911', 'Alternative/Rock', 'Idol Mungpi', 'Sha Mwe LA', [['ZOMI KA HIH ANGTANG ING', 'QZXLZ2532889', '4:44']], 'El-Shaddai Music Production'),
            $make('2138158', 'TELEMUSIC2500070', 'LUNG KIA KEI OU', 'Idol Mungpi', 'single', '2025-08-22', '2025-08-20', '8447536339198', 'Alternative', 'Idol Mungpi', 'Sha Mwe LA', [['LUNG KIA KEI OU', 'QZXLZ2536917', '3:36']]),
            $make('2244255', 'TELEMUSIC2500108', 'Nge Tone Ka', 'Lian Gel', 'single', '2025-11-12', '2025-11-11', '8447536678228', 'Pop', 'Lian Gel', 'Sha Mwe LA', [['Nge Tone Ka', 'QZXLZ2553841', '3:18']]),
            $make('2258746', 'TELEMUSIC2500110', 'နွေဦးလေ', 'Pau Pi (TP)', 'single', '2025-11-22', '2025-11-20', '8447536737055', 'Country', 'Pau Pi (TP)', 'Sha Mwe LA', [['နွေဦးလေ', 'QZXLZ2557683', '4:22']]),
            $make('2261314', 'TELEMUSIC2500109', 'Itna Sinthu', 'Lian Pu', 'single', '2025-11-17', '2025-11-20', '8447536737031', 'Pop/Singer Songwriter', 'Lian Pu', 'KHAIPII', [['Itna Sinthu', 'QZXLZ2557682', '3:23']]),
            $make('2267941', 'TELEMUSIC2500112', 'Ta Nae Nae Tot', 'Myo Thant', 'single', '2025-11-28', '2025-12-02', '8447536770175', 'Rock/Classic', 'Myo Thant', 'Sha Mwe LA', [['Ta Nae Nae Tot', 'QZXLZ2558793', '4:05']]),
            $make('2271194', 'TELEMUSIC2500113', 'Hong Ngai Ung TOPA', 'David KS Piangno', 'ep', '2025-11-29', '2025-12-02', '8447536770182', 'Country', 'David KS Piangno', 'Sha Mwe LA', [['Hong Ngai Ung TOPA', 'QZXLZ2558794', '4:19'], ['Hong Ngai Ung TOPA (Instrumental)', 'QZXLZ2558795', '4:26']]),
            $make('2068629', 'TELEMUSIC2500062', 'Hnangamnak Nun Thup', 'Salai ST Luaite', 'album', '2025-06-30', '2025-06-30', '8447536135691', 'Spiritual/Gospel', 'Salai ST Luaite', 'Sha Mwe LA', [
                ['Duh Ding Umsun', 'QZXLZ2526886', '4:34'], ['Special Bik', 'QZXLZ2526887', '3:58'], ['Pathian Dawtnak', 'QZXLZ2526888', '4:17'], ['Amen, Rung Tum Thlang Aw!', 'QZXLZ2526889', '3:46'], ['Hnangamnak Nun Thup', 'QZXLZ2526890', '2:20'], ['Aw Cross', 'QZXLZ2526891', '4:53'], ['Mission Tuantu Ka Pa', 'QZXLZ2526892', '4:41'], ['Ka Nun Khawng Hruaitu', 'QZXLZ2526893', '5:39'], ['Lalpa Thatna', 'QZXLZ2526894', '5:08'], ['Hallelujah Amen', 'QZXLZ2526895', '5:52'], ['Bonus', 'QZXLZ2526896', '3:53'],
            ]),
            $make('2434228', 'TELEMUSIC2600153', 'TUUN SUNG KHAT', 'Joseph Piang', 'ep', '2026-04-19', '2026-06-10', '8447721576896', 'Country/Pop', 'Joseph Piang', 'Sha Mwe LA', [['TUUN SUNG KHAT', 'QZNW72630880', '3:39'], ['TUUN SUNG KHAT (Instrumental)', 'QZNW72630881', '3:39']]),
            $make('2466369', 'TELEMUSIC2600142', 'A Hoih Zaw', 'Cyi Thang', 'ep', '2026-04-07', '2026-04-08', '8447721282681', 'Spiritual/Gospel', 'Thawngpi', 'Sha Mwe LA', [['A Hoih Zaw', 'QZNW72619028', '4:58'], ['A Hoih Zaw (Instrumental)', 'QZNW72619029', '4:58']]),
            $make('2476191', 'TELEMUSIC2600143', 'နွေဦးလေရူး', 'Pau Pi (TP)', 'single', '2026-04-11', '2026-04-08', '8447721282698', 'Pop/Contemporary/Adult', 'Pau Pi (TP)', 'Sha Mwe LA', [['နွေဦးလေရူး', 'QZNW72619030', '4:22']]),
        ];
    }

    public function down(): void
    {
        // Preserve imported historical catalog on rollback.
    }
};
