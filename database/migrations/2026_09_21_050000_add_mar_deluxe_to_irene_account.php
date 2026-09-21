<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL = 'irenezinmarmyint20224@gmail.com';
    private const UPC = '8447536112975';

    public function up(): void
    {
        $user = DB::table('users')->where('email', self::EMAIL)->first();
        if (! $user) {
            return;
        }

        $artist = DB::table('artists')->where('user_id', $user->id)->first();
        if (! $artist) {
            return;
        }

        DB::transaction(function () use ($artist) {
            $now = now();
            $albumValues = [
                'title' => 'MAR (Deluxe)',
                'slug' => 'mar-deluxe-irene-zin-mar-myint',
                'release_type' => 'album',
                'cover_art' => 'cover_art/mar-deluxe-irene-zin-mar-myint.jpg',
                'genre' => 'Pop',
                'label' => 'TeleMusic',
                'release_date' => '2025-06-17',
                'upc_code' => self::UPC,
                'status' => 'draft',
                'copyright_holder' => '2025 Irene Zin Mar Myint',
                'phonogram_right_holder' => '2025 Irene Zin Mar Myint',
                'request_new_isrc' => false,
                'approved_at' => null,
                'notes' => 'Reference: TELEMUSIC2500047. Spotify: https://open.spotify.com/album/4vidihIiGqaXbhtKS7P0Mv',
                'updated_at' => $now,
            ];

            $album = DB::table('albums')
                ->where('artist_id', $artist->id)
                ->where('upc_code', self::UPC)
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

            $credits = json_encode([$artist->artist_name], JSON_UNESCAPED_UNICODE);
            $tracks = [
                ['QZXLZ2526056', 'Chit Tal Pyaw Tae Ka Sar Nee', '4:21'],
                ['QZXLZ2526057', 'Ngar Ma Shi Tot Yin', '5:15'],
                ['QZXLZ2526058', 'Name', '4:31'],
                ['QZXLZ2526059', 'A Yate Ta Khu Lo Nay Par Mal', '4:41'],
                ['QZXLZ2526060', 'Wut Kway', '4:31'],
                ['QZXLZ2526061', 'Mar', '3:25'],
                ['QZXLZ2526062', 'Hate', '3:26'],
                ['QZXLZ2526063', 'Oo Sone A Chit', '4:23'],
                ['QZXLZ2526064', 'CRUSH IS MY FRIEND', '4:15'],
                ['QZXLZ2526065', 'Dan Yar Lon A Mone', '4:11'],
                ['QZXLZ2526066', 'Chit Thu Swal Arr', '3:51'],
                ['QZXLZ2526067', 'Yin Bat Htae Ka Lu Nha Yout', '3:47'],
                ['QZXLZ2526068', 'Nout Sone Achit', '3:45'],
            ];

            foreach ($tracks as $index => [$isrc, $title, $duration]) {
                $songValues = [
                    'album_id' => $albumId,
                    'artist_id' => $artist->id,
                    'title' => $title,
                    'version' => 'Deluxe',
                    'track_number' => $index + 1,
                    'duration' => $duration,
                    'isrc_code' => $isrc,
                    'request_new_isrc' => false,
                    'explicit' => false,
                    'genre' => 'Pop',
                    'primary_artists' => $credits,
                    'composers' => $credits,
                    'lyricist' => $credits,
                    'producers' => $credits,
                    'vocals' => $credits,
                    'status' => 'draft',
                    'updated_at' => $now,
                ];

                $song = DB::table('songs')->where('isrc_code', $isrc)->first();
                if ($song) {
                    DB::table('songs')->where('id', $song->id)->update($songValues);
                    $songId = $song->id;
                } else {
                    $songId = DB::table('songs')->insertGetId($songValues + ['created_at' => $now]);
                }

            }
        });
    }

    public function down(): void
    {
        $artistId = DB::table('users')
            ->join('artists', 'artists.user_id', '=', 'users.id')
            ->where('users.email', self::EMAIL)
            ->value('artists.id');

        if ($artistId) {
            DB::table('albums')->where('artist_id', $artistId)->where('upc_code', self::UPC)->delete();
        }
    }
};
