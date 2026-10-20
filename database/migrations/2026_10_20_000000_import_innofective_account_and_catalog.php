<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    private const EMAIL = 'innofective@gmail.com';

    private const PASSWORD_HASH = '$2y$12$b19kctiJgWfBOfiPO2z0Q.jaXs5czSoWK77uYovRSAvT9leVfd7Y.';

    public function up(): void
    {
        foreach ($this->releases() as $release) {
            $source = database_path('data/innofective/'.$release['cover']);
            if (! is_file($source)) {
                throw new RuntimeException('Innofective cover asset is missing: '.$source);
            }
            Storage::disk('public')->put('cover_art/innofective/'.$release['cover'], file_get_contents($source));
        }

        DB::transaction(function (): void {
            $now = now();
            $user = DB::table('users')->whereRaw('LOWER(email) = ?', [self::EMAIL])->first();
            $userValues = [
                'name' => 'Tin Aung Htun',
                'email' => self::EMAIL,
                'email_verified_at' => $now,
                'phone' => '+959442626510',
                'role' => 'manager',
                'is_active' => true,
                'updated_at' => $now,
            ];

            if ($user) {
                DB::table('users')->where('id', $user->id)->update($userValues);
                $userId = $user->id;
            } else {
                $userId = DB::table('users')->insertGetId($userValues + [
                    'password' => self::PASSWORD_HASH,
                    'created_at' => $now,
                ]);
            }

            $master = DB::table('master_accounts')->where('owner_user_id', $userId)->first();
            $masterValues = [
                'name' => 'Innofective',
                'country' => 'Myanmar',
                'email' => self::EMAIL,
                'phone' => '+959442626510',
                'platform_fee_percentage' => 15,
                'default_management_fee_percentage' => 0,
                'maximum_management_fee_percentage' => 30,
                'is_active' => true,
                'updated_at' => $now,
            ];
            if ($master) {
                DB::table('master_accounts')->where('id', $master->id)->update($masterValues);
                $masterId = $master->id;
            } else {
                $masterId = DB::table('master_accounts')->insertGetId($masterValues + [
                    'owner_user_id' => $userId,
                    'created_at' => $now,
                ]);
            }

            foreach ($this->releases() as $release) {
                $artistId = $this->upsertArtist($userId, $release, $now);
                DB::table('master_account_artist')->updateOrInsert(
                    ['master_account_id' => $masterId, 'artist_id' => $artistId],
                    [
                        'management_fee_percentage' => 0,
                        'access_level' => 'full_access',
                        'status' => 'active',
                        'created_by' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
                $this->upsertRelease($artistId, $release, $now);
            }
        });
    }

    private function upsertArtist(int $userId, array $release, mixed $now): int
    {
        $artist = DB::table('artists')
            ->where('created_by_user_id', $userId)
            ->where('artist_name', $release['artist'])
            ->first();
        $values = [
            'artist_name' => $release['artist'],
            'genre' => $release['genre'],
            'country' => 'Myanmar',
            'payment_email' => self::EMAIL,
            'revenue_share_percentage' => 85,
            'updated_at' => $now,
        ];

        if ($artist) {
            DB::table('artists')->where('id', $artist->id)->update($values);

            return $artist->id;
        }

        return DB::table('artists')->insertGetId($values + [
            'user_id' => null,
            'created_by_user_id' => $userId,
            'created_at' => $now,
        ]);
    }

    private function upsertRelease(int $artistId, array $release, mixed $now): void
    {
        $album = DB::table('albums')->where('artist_id', $artistId)->where('upc_code', $release['upc'])->first();
        $albumValues = [
            'title' => $release['title'],
            'slug' => $release['slug'],
            'release_type' => 'single',
            'cover_art' => 'cover_art/innofective/'.$release['cover'],
            'genre' => $release['genre'],
            'label' => $release['label'],
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
            'notes' => 'Imported approved SonoSuite single. Reference: '.$release['reference'].'. Source album ID: '.$release['source_id'].'. '.$release['notes'],
            'updated_at' => $now,
        ];

        if ($album) {
            DB::table('albums')->where('id', $album->id)->update($albumValues);
            $albumId = $album->id;
        } else {
            $albumId = DB::table('albums')->insertGetId($albumValues + ['artist_id' => $artistId, 'created_at' => $now]);
        }

        $songValues = [
            'album_id' => $albumId,
            'artist_id' => $artistId,
            'title' => $release['title'],
            'track_number' => 1,
            'duration' => $release['duration'],
            'isrc_code' => $release['isrc'],
            'request_new_isrc' => false,
            'explicit' => false,
            'genre' => $release['genre'],
            'language' => 'Burmese',
            'primary_artists' => json_encode([$release['artist']], JSON_UNESCAPED_UNICODE),
            'composers' => json_encode($release['composers'], JSON_UNESCAPED_UNICODE),
            'lyricist' => json_encode($release['lyricists'], JSON_UNESCAPED_UNICODE),
            'producers' => json_encode($release['producers'], JSON_UNESCAPED_UNICODE),
            'vocals' => json_encode([$release['artist']], JSON_UNESCAPED_UNICODE),
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
                'artist' => 'Awesome BoyZ', 'title' => 'Min Nae Achit', 'slug' => 'min-nae-achit-awesome-boyz',
                'genre' => 'Pop', 'label' => 'BNLK', 'release_date' => '2025-07-21', 'approved_at' => '2025-07-15',
                'copyright' => '2025 Awesome BoyZ', 'phonogram' => '2025 BNLK',
                'upc' => '8447536202805', 'isrc' => 'GX8LE2442811', 'duration' => '4:27',
                'composers' => ['PAII', 'Joshua'], 'lyricists' => ['Awesome BoyZ'], 'producers' => ['Bhone Nay Linn Kai'],
                'reference' => 'TELEMUSIC2500066', 'source_id' => '2097980', 'cover' => 'min-nae-achit.jpg',
                'notes' => 'Programming, mixing and mastering engineer: Cracky.',
            ],
            [
                'artist' => 'Parallax', 'title' => 'Tekno Journey', 'slug' => 'tekno-journey-parallax',
                'genre' => 'Electronic/Hard Techno', 'label' => 'TeleMusic', 'release_date' => '2025-07-28', 'approved_at' => '2025-07-29',
                'copyright' => '2025 Parallax', 'phonogram' => '2025 innofective',
                'upc' => '8447536257959', 'isrc' => 'QZXLZ2532892', 'duration' => '4:29',
                'composers' => ['Parallax'], 'lyricists' => ['Parallax'], 'producers' => ['Parallax'],
                'reference' => 'TELEMUSIC2500068', 'source_id' => '2111579', 'cover' => 'tekno-journey.jpg',
                'notes' => 'Programming, mixing and mastering engineer: Parallax.',
            ],
        ];
    }

    public function down(): void
    {
        // Preserve the imported production account and catalog on rollback.
    }
};
