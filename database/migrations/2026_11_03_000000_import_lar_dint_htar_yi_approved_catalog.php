<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const EMAIL = 'lardint00@gmail.com';

    private const ARTIST_NAME = 'Lar Dint Htar Yi';

    public function up(): void
    {
        foreach ($this->releases() as $release) {
            $source = database_path('data/lar_dint_htar_yi_covers/'.$release['cover']);
            if (! is_file($source)) {
                throw new RuntimeException('Lar Dint Htar Yi cover asset is missing: '.$source);
            }

            Storage::disk('public')->put($this->coverPath($release), file_get_contents($source));
        }

        DB::transaction(function (): void {
            $now = now();
            $user = DB::table('users')->whereRaw('LOWER(email) = ?', [self::EMAIL])->first();
            $userValues = [
                'email' => self::EMAIL,
                'email_verified_at' => $user?->email_verified_at ?: $now,
                'role' => 'artist',
                'is_active' => true,
                'updated_at' => $now,
            ];

            if ($user) {
                if (! filled($user->name)) {
                    $userValues['name'] = self::ARTIST_NAME;
                }
                DB::table('users')->where('id', $user->id)->update($userValues);
                $userId = $user->id;
            } else {
                $userId = DB::table('users')->insertGetId($userValues + [
                    'name' => self::ARTIST_NAME,
                    'password' => Hash::make(Str::random(64)),
                    'created_at' => $now,
                ]);
            }

            $artist = DB::table('artists')->where('user_id', $userId)->first();
            $artistValues = [
                'artist_name' => self::ARTIST_NAME,
                'genre' => 'Pop',
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

            foreach ($this->releases() as $release) {
                $this->upsertRelease($artistId, $release, $now);
            }
        });
    }

    private function upsertRelease(int $artistId, array $release, mixed $now): void
    {
        $album = DB::table('albums')
            ->where('artist_id', $artistId)
            ->where('upc_code', $release['upc'])
            ->first();
        $albumValues = [
            'title' => $release['title'],
            'slug' => $release['slug'],
            'release_type' => $release['type'],
            'cover_art' => $this->coverPath($release),
            'genre' => 'Pop',
            'label' => 'TeleMusic',
            'release_date' => $release['release_date'],
            'price' => 0,
            'selected_store_ids' => json_encode([]),
            'selected_addons' => json_encode([]),
            'payment_status' => 'paid',
            'upc_code' => $release['upc'],
            'status' => 'approved',
            'copyright_holder' => '2025 '.self::ARTIST_NAME,
            'phonogram_right_holder' => '2025 '.self::ARTIST_NAME,
            'request_new_isrc' => false,
            'approved_at' => $release['approved_at'].' 00:00:00',
            'notes' => 'Imported approved SonoSuite release. Reference: '.$release['reference'].'. Source album ID: '.$release['source_id'].'.',
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

        $songValues = [
            'album_id' => $albumId,
            'artist_id' => $artistId,
            'title' => $release['title'],
            'track_number' => 1,
            'duration' => $release['duration'],
            'isrc_code' => $release['isrc'],
            'request_new_isrc' => false,
            'explicit' => false,
            'genre' => 'Pop',
            'language' => 'Burmese',
            'primary_artists' => json_encode([self::ARTIST_NAME], JSON_UNESCAPED_UNICODE),
            'composers' => json_encode([$release['composer']], JSON_UNESCAPED_UNICODE),
            'lyricist' => json_encode([$release['lyricist']], JSON_UNESCAPED_UNICODE),
            'producers' => json_encode([$release['producer']], JSON_UNESCAPED_UNICODE),
            'vocals' => json_encode($release['vocals'], JSON_UNESCAPED_UNICODE),
            'featuring' => json_encode($release['featuring'], JSON_UNESCAPED_UNICODE),
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

    private function coverPath(array $release): string
    {
        return 'cover_art/lar-dint-htar-yi/'.$release['cover'];
    }

    private function releases(): array
    {
        $artistVocals = [self::ARTIST_NAME];

        return [
            ['title' => 'Maung A Sin Pyay Yae Lar', 'slug' => 'maung-a-sin-pyay-yae-lar-lar-dint-htar-yi', 'type' => 'single', 'release_date' => '2025-08-29', 'approved_at' => '2025-08-26', 'upc' => '8447536358366', 'isrc' => 'NLRD52308796', 'duration' => '4:48', 'composer' => 'Shine Wai Yan', 'lyricist' => 'Shine Wai Yan', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500072', 'source_id' => '2149492', 'cover' => 'maung-a-sin-pyay-yae-lar.jpg'],
            ['title' => 'D Nae Min Ko Po Lwan Tal', 'slug' => 'd-nae-min-ko-po-lwan-tal-lar-dint-htar-yi', 'type' => 'single', 'release_date' => '2025-09-10', 'approved_at' => '2025-09-25', 'upc' => '8447536472451', 'isrc' => 'NLRD52308793', 'duration' => '4:22', 'composer' => 'Khai No', 'lyricist' => 'Khai No', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500094', 'source_id' => '2166492', 'cover' => 'd-nae-min-ko-po-lwan-tal.jpg'],
            ['title' => 'A Nan Lay Nat See Kyo Mal', 'slug' => 'a-nan-lay-nat-see-kyo-mal-lar-dint-htar-yi', 'type' => 'single', 'release_date' => '2025-09-08', 'approved_at' => '2025-09-25', 'upc' => '8447536472420', 'isrc' => 'NLRD52308786', 'duration' => '4:27', 'composer' => 'Kaung Kaung', 'lyricist' => 'Kaung Kaung', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500091', 'source_id' => '2166543', 'cover' => 'a-nan-lay-nat-see-kyo-mal.jpg'],
            ['title' => 'Htet Chit Phyit Khae Yin', 'slug' => 'htet-chit-phyit-khae-yin-lar-dint-htar-yi', 'type' => 'single', 'release_date' => '2025-09-13', 'approved_at' => '2025-09-25', 'upc' => '8447536472413', 'isrc' => 'NLRD52308794', 'duration' => '4:19', 'composer' => 'Khai No', 'lyricist' => 'Khai No', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500090', 'source_id' => '2172812', 'cover' => 'htet-chit-phyit-khae-yin.jpg'],
            ['title' => 'Nin Ma Shi Loh Ma Phyit Tae Ngar', 'slug' => 'nin-ma-shi-loh-ma-phyit-tae-ngar-lar-dint-htar-yi', 'type' => 'album', 'release_date' => '2025-09-16', 'approved_at' => '2025-09-25', 'upc' => '8447536472468', 'isrc' => 'NLRD52308787', 'duration' => '3:34', 'composer' => 'Noe Noe Win Chhun', 'lyricist' => 'Noe Noe Win Chhun', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500095', 'source_id' => '2174739', 'cover' => 'nin-ma-shi-loh-ma-phyit-tae-ngar.jpg'],
            ['title' => 'A Chit Ma Shi Tae Bawa', 'slug' => 'a-chit-ma-shi-tae-bawa-lar-dint-htar-yi', 'type' => 'album', 'release_date' => '2025-09-16', 'approved_at' => '2025-09-25', 'upc' => '8447536472475', 'isrc' => 'NLRD52308788', 'duration' => '3:20', 'composer' => 'Aung Myint Myat', 'lyricist' => 'Aung Myint Myat', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500096', 'source_id' => '2174767', 'cover' => 'a-chit-ma-shi-tae-bawa.jpg'],
            ['title' => 'Min Ma Shi Tae Nout', 'slug' => 'min-ma-shi-tae-nout-lar-dint-htar-yi', 'type' => 'album', 'release_date' => '2025-09-16', 'approved_at' => '2025-09-25', 'upc' => '8447536472406', 'isrc' => 'NLRD52308789', 'duration' => '4:04', 'composer' => 'Thet Tant', 'lyricist' => 'Thet Tant', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500089', 'source_id' => '2174773', 'cover' => 'min-ma-shi-tae-nout.jpg'],
            ['title' => 'Thamee Kyo Sar Nay Par Tal', 'slug' => 'thamee-kyo-sar-nay-par-tal-lar-dint-htar-yi', 'type' => 'album', 'release_date' => '2025-09-16', 'approved_at' => '2025-09-25', 'upc' => '8447536472444', 'isrc' => 'NLRD52308790', 'duration' => '5:31', 'composer' => 'Noe Noe Win Chhun', 'lyricist' => 'Noe Noe Win Chhun', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500093', 'source_id' => '2174783', 'cover' => 'thamee-kyo-sar-nay-par-tal.jpg'],
            ['title' => 'Christmas A Date Pal', 'slug' => 'christmas-a-date-pal-lar-dint-htar-yi', 'type' => 'single', 'release_date' => '2025-09-16', 'approved_at' => '2025-09-25', 'upc' => '8447536472376', 'isrc' => 'NLRD52308791', 'duration' => '4:08', 'composer' => 'Zai Latt', 'lyricist' => 'Zai Latt', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500086', 'source_id' => '2174789', 'cover' => 'christmas-a-date-pal.jpg'],
            ['title' => 'Daung Yin Pyan Hmar Yay Tae Sar', 'slug' => 'daung-yin-pyan-hmar-yay-tae-sar-lar-dint-htar-yi', 'type' => 'single', 'release_date' => '2025-09-16', 'approved_at' => '2025-09-25', 'upc' => '8447536472437', 'isrc' => 'NLRD52308792', 'duration' => '4:39', 'composer' => 'Myint Moe Aung', 'lyricist' => 'Myint Moe Aung', 'producer' => 'Myint Moe Aung', 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500092', 'source_id' => '2174794', 'cover' => 'daung-yin-pyan-hmar-yay-tae-sar.jpg'],
            ['title' => 'Kyay Zuu Pal MayMay', 'slug' => 'kyay-zuu-pal-maymay-lar-dint-htar-yi', 'type' => 'single', 'release_date' => '2025-09-16', 'approved_at' => '2025-09-25', 'upc' => '8447536472383', 'isrc' => 'NLRD52308795', 'duration' => '4:47', 'composer' => 'L Zaw La', 'lyricist' => 'L Zaw La', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500087', 'source_id' => '2174800', 'cover' => 'kyay-zuu-pal-maymay.jpg'],
            ['title' => 'Kyoe', 'slug' => 'kyoe-lar-dint-htar-yi', 'type' => 'single', 'release_date' => '2025-09-16', 'approved_at' => '2025-09-25', 'upc' => '8447536472369', 'isrc' => 'NLRD52309131', 'duration' => '4:45', 'composer' => 'Khai No', 'lyricist' => 'Khai No', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500085', 'source_id' => '2174819', 'cover' => 'kyoe.jpg'],
            ['title' => 'Nga Thit Sar Tayar', 'slug' => 'nga-thit-sar-tayar-lar-dint-htar-yi', 'type' => 'single', 'release_date' => '2025-09-16', 'approved_at' => '2025-09-25', 'upc' => '8447536472352', 'isrc' => 'NLRD52425918', 'duration' => '4:09', 'composer' => 'Saw Ko Ko Chit', 'lyricist' => 'Saw Ko Ko Chit', 'producer' => self::ARTIST_NAME, 'vocals' => $artistVocals, 'featuring' => [], 'reference' => 'TELEMUSIC2500084', 'source_id' => '2174824', 'cover' => 'nga-thit-sar-tayar.jpg'],
            ['title' => 'Ma Pyaung Lel Khae Par', 'slug' => 'ma-pyaung-lel-khae-par-lar-dint-htar-yi', 'type' => 'single', 'release_date' => '2025-09-16', 'approved_at' => '2025-09-25', 'upc' => '8447536472345', 'isrc' => 'NLRD52349869', 'duration' => '3:29', 'composer' => 'Saw Lay Shine', 'lyricist' => 'Saw Lay Shine', 'producer' => self::ARTIST_NAME, 'vocals' => [self::ARTIST_NAME, 'Saw Eh'], 'featuring' => ['Saw Eh'], 'reference' => 'TELEMUSIC2500083', 'source_id' => '2174850', 'cover' => 'ma-pyaung-lel-khae-par.jpg'],
            ['title' => 'A Lay A Nat', 'slug' => 'a-lay-a-nat-lar-dint-htar-yi', 'type' => 'single', 'release_date' => '2025-09-16', 'approved_at' => '2025-09-25', 'upc' => '8447536472338', 'isrc' => 'NLRD52352165', 'duration' => '4:30', 'composer' => 'Bz', 'lyricist' => 'Bz', 'producer' => self::ARTIST_NAME, 'vocals' => [self::ARTIST_NAME, 'Ge Daung'], 'featuring' => ['Ge Daung'], 'reference' => 'TELEMUSIC2500082', 'source_id' => '2174863', 'cover' => 'a-lay-a-nat.jpg'],
        ];
    }

    public function down(): void
    {
        // Preserve the imported production account and catalog on rollback.
    }
};
