<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const EMAIL = 'lingkyek@gmail.com';

    private const RELEASE_TITLE = 'မိုက်ခဲ';

    public function up(): void
    {
        $source = database_path('data/kyek_pha/maik-khae.jpg');
        if (! is_file($source)) {
            throw new RuntimeException('Kyek Pha cover asset is missing: '.$source);
        }

        $coverPath = 'cover_art/kyek-pha/maik-khae.jpg';
        Storage::disk('public')->put($coverPath, file_get_contents($source));

        DB::transaction(function () use ($coverPath): void {
            $now = now();
            $user = DB::table('users')->whereRaw('LOWER(email) = ?', [self::EMAIL])->first();
            $userValues = [
                'name' => 'Thant Zaw',
                'email' => self::EMAIL,
                'email_verified_at' => $now,
                'role' => 'artist',
                'is_active' => true,
                'updated_at' => $now,
            ];

            if ($user) {
                DB::table('users')->where('id', $user->id)->update($userValues);
                $userId = $user->id;
            } else {
                $userId = DB::table('users')->insertGetId($userValues + [
                    'password' => Hash::make(Str::random(64)),
                    'created_at' => $now,
                ]);
            }

            $artist = DB::table('artists')->where('user_id', $userId)->first();
            $artistValues = [
                'artist_name' => 'Kyek Pha',
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
                ->where('slug', 'maik-khae-kyek-pha')
                ->first();
            $albumValues = [
                'title' => self::RELEASE_TITLE,
                'slug' => 'maik-khae-kyek-pha',
                'release_type' => 'single',
                'cover_art' => $coverPath,
                'genre' => 'Hip-Hop/Rap',
                'label' => 'TeleMusic',
                'release_date' => '2026-10-21',
                'price' => 0,
                'selected_store_ids' => json_encode([]),
                'selected_addons' => json_encode([]),
                'payment_status' => 'paid',
                'status' => 'approved',
                'copyright_holder' => '2026 Kyek Pha',
                'phonogram_right_holder' => '2026 Kyek Pha',
                'request_new_isrc' => false,
                'approved_at' => '2026-10-21 00:00:00',
                'notes' => 'Imported approved catalog release from supplied artwork and track metadata.',
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
                    'explicit' => false,
                    'genre' => 'Hip-Hop/Rap',
                    'language' => $track['instrumental'] ? 'Instrumental' : 'Burmese',
                    'primary_artists' => json_encode(['Kyek Pha'], JSON_UNESCAPED_UNICODE),
                    'composers' => json_encode([], JSON_UNESCAPED_UNICODE),
                    'lyricist' => json_encode([], JSON_UNESCAPED_UNICODE),
                    'producers' => json_encode([], JSON_UNESCAPED_UNICODE),
                    'vocals' => json_encode($track['instrumental'] ? [] : ['Kyek Pha'], JSON_UNESCAPED_UNICODE),
                    'featuring' => json_encode([], JSON_UNESCAPED_UNICODE),
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
            ['title' => self::RELEASE_TITLE, 'isrc' => 'QT66C2603426', 'duration' => '4:43', 'instrumental' => false],
            ['title' => self::RELEASE_TITLE.' (Instrumental)', 'isrc' => 'QT66C2603427', 'duration' => '4:44', 'instrumental' => true],
        ];
    }

    public function down(): void
    {
        // Preserve the imported production account and catalog on rollback.
    }
};
