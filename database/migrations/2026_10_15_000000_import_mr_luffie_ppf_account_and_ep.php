<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    private const EMAIL = 'sheinwinthtet6@gmail.com';

    private const PASSWORD_HASH = '$2y$10$SAi4CxMjaBzMzuX.HUJWvOOlmn7N1V84gVV8iX2jH1iOCkyyCQD0u';

    private const UPC = '8447721188129';

    public function up(): void
    {
        $source = database_path('data/mr_luffie/ppf.jpg');
        if (! is_file($source)) {
            throw new RuntimeException('P.P.F cover asset is missing: '.$source);
        }

        $coverPath = 'cover_art/mr-luffie/ppf.jpg';
        Storage::disk('public')->put($coverPath, file_get_contents($source));

        DB::transaction(function () use ($coverPath): void {
            $now = now();
            $user = DB::table('users')->where('email', self::EMAIL)->first();
            $userValues = [
                'name' => 'Mr.Luffie',
                'email' => self::EMAIL,
                'email_verified_at' => $now,
                'password' => self::PASSWORD_HASH,
                'role' => 'artist',
                'is_active' => true,
                'updated_at' => $now,
            ];

            if ($user) {
                DB::table('users')->where('id', $user->id)->update($userValues);
                $userId = $user->id;
            } else {
                $userId = DB::table('users')->insertGetId($userValues + ['created_at' => $now]);
            }

            $artist = DB::table('artists')->where('user_id', $userId)->first();
            $artistValues = [
                'artist_name' => 'Mr.Luffie',
                'genre' => 'Hip-Hop/Rap',
                'country' => 'Myanmar',
                'payment_email' => self::EMAIL,
                'revenue_share_percentage' => 85,
                'updated_at' => $now,
            ];

            if ($artist) {
                DB::table('artists')->where('id', $artist->id)->update($artistValues);
                $artistId = $artist->id;
            } else {
                $artistId = DB::table('artists')->insertGetId($artistValues + [
                    'user_id' => $userId,
                    'created_at' => $now,
                ]);
            }

            $album = DB::table('albums')
                ->where('artist_id', $artistId)
                ->where('upc_code', self::UPC)
                ->first();
            $albumValues = [
                'title' => 'P.P.F',
                'slug' => 'p-p-f-mr-luffie',
                'release_type' => 'ep',
                'cover_art' => $coverPath,
                'genre' => 'Hip-Hop/Rap',
                'label' => 'TeleMusic',
                'release_date' => '2026-03-22',
                'price' => 0,
                'selected_store_ids' => json_encode([]),
                'selected_addons' => json_encode([]),
                'payment_status' => 'paid',
                'upc_code' => self::UPC,
                'status' => 'approved',
                'copyright_holder' => '2026 Mr.Luffie',
                'phonogram_right_holder' => '2026 Htet Wint Shein',
                'request_new_isrc' => false,
                'approved_at' => '2026-03-12 00:00:00',
                'notes' => 'Imported approved SonoSuite EP. Reference: TELEMUSIC2600136. Source album ID: 2436167. Published cover verified by UPC.',
                'updated_at' => $now,
            ];

            if ($album) {
                DB::table('albums')->where('id', $album->id)->update($albumValues);
                $albumId = $album->id;
            } else {
                $albumId = DB::table('albums')->insertGetId($albumValues + [
                    'artist_id' => $artistId,
                    'created_at' => $now,
                ]);
            }

            foreach ($this->tracks() as $index => $track) {
                $songValues = [
                    'album_id' => $albumId,
                    'artist_id' => $artistId,
                    'title' => $track['title'],
                    'track_number' => $index + 1,
                    'duration' => $track['duration'],
                    'isrc_code' => $track['isrc'],
                    'request_new_isrc' => false,
                    'explicit' => true,
                    'genre' => 'Hip-Hop/Rap',
                    'language' => 'English',
                    'primary_artists' => json_encode(['Mr.Luffie'], JSON_UNESCAPED_UNICODE),
                    'composers' => json_encode($track['composers'], JSON_UNESCAPED_UNICODE),
                    'lyricist' => json_encode($track['lyricists'], JSON_UNESCAPED_UNICODE),
                    'producers' => json_encode($track['producers'], JSON_UNESCAPED_UNICODE),
                    'vocals' => json_encode($track['vocals'], JSON_UNESCAPED_UNICODE),
                    'featuring' => json_encode($track['featuring'], JSON_UNESCAPED_UNICODE),
                    'status' => 'approved',
                    'updated_at' => $now,
                ];

                $song = DB::table('songs')->where('isrc_code', $track['isrc'])->first();
                if ($song) {
                    DB::table('songs')->where('id', $song->id)->update($songValues);
                } else {
                    DB::table('songs')->insert($songValues + ['created_at' => $now]);
                }
            }
        });
    }

    private function tracks(): array
    {
        return [
            [
                'title' => 'Act A Dummy', 'isrc' => 'QZNW72615042', 'duration' => '4:40',
                'composers' => ['Zwe Thet Paing', 'Htet Wint Shein', 'TayZar Sawthein'],
                'lyricists' => ['Zwe Thet Paing', 'Htet Wint Shein', 'TayZar Sawthein'],
                'producers' => ['THM'], 'vocals' => ['Mr.Luffie', 'Buthee', 'Zwe Thet Paing'],
                'featuring' => ['Buthee', 'Zwe Thet Paing'],
            ],
            [
                'title' => 'Mofo', 'isrc' => 'QZNW72615043', 'duration' => '1:48',
                'composers' => ['Htet Wint Shein'], 'lyricists' => ['Htet Wint Shein'],
                'producers' => ['Mr.Luffie'], 'vocals' => ['Mr.Luffie'], 'featuring' => [],
            ],
            [
                'title' => 'The Wall', 'isrc' => 'QZNW72615044', 'duration' => '2:00',
                'composers' => ['Htet Wint Shein'], 'lyricists' => ['Htet Wint Shein'],
                'producers' => ['Ye Thu'], 'vocals' => ['Mr.Luffie'], 'featuring' => [],
            ],
            [
                'title' => 'Paranoia', 'isrc' => 'QZNW72615045', 'duration' => '3:00',
                'composers' => ['Htet Wint Shein'], 'lyricists' => ['Htet Wint Shein'],
                'producers' => ['Mr.Luffie'], 'vocals' => ['Mr.Luffie'], 'featuring' => [],
            ],
            [
                'title' => 'Dolphin', 'isrc' => 'QZNW72615046', 'duration' => '2:18',
                'composers' => ['Htet Wint Shein'], 'lyricists' => ['Htet Wint Shein'],
                'producers' => ['Mr.Luffie'], 'vocals' => ['Mr.Luffie'], 'featuring' => [],
            ],
        ];
    }

    public function down(): void
    {
        // Preserve the imported production account and catalog on rollback.
    }
};
