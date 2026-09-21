<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const EMAIL = 'irenezinmarmyint20224@gmail.com';

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

        DB::transaction(function () use ($artist) {
            foreach ($this->releases() as $release) {
                $this->upsertRelease($artist, $release);
            }

            // The immediately preceding catalog migration imported MAR as a draft.
            // This request approves the artist's complete imported catalog.
            $marId = DB::table('albums')
                ->where('artist_id', $artist->id)
                ->where('upc_code', '8447536112975')
                ->value('id');

            if ($marId) {
                DB::table('albums')->where('id', $marId)->update([
                    'status' => 'approved',
                    'approved_at' => '2025-06-23 00:00:00',
                    'updated_at' => now(),
                ]);
                DB::table('songs')->where('album_id', $marId)->update([
                    'status' => 'approved',
                    'updated_at' => now(),
                ]);
            }
        });
    }

    private function upsertRelease(object $artist, array $release): void
    {
        $now = now();
        $slug = Str::slug($release['title']).'-irene-zin-mar-myint';
        $albumValues = [
            'title' => $release['title'],
            'slug' => $slug,
            'release_type' => $release['type'],
            'cover_art' => 'cover_art/'.$release['cover'].'.jpg',
            'genre' => $release['genre'] ?? 'Pop',
            'label' => 'TeleMusic',
            'release_date' => $release['date'],
            'upc_code' => $release['upc'],
            'status' => 'approved',
            'copyright_holder' => substr($release['date'], 0, 4).' Irene Zin Mar Myint',
            'phonogram_right_holder' => substr($release['date'], 0, 4).' Irene Zin Mar Myint',
            'request_new_isrc' => false,
            'approved_at' => $release['approved'].' 00:00:00',
            'notes' => 'SonoSuite reference: '.$release['reference'].'. Source album ID: '.$release['source_id'].'.',
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

        foreach ($release['tracks'] as $index => $track) {
            $primary = $track['primary'] ?? ['Irene Zin Mar Myint'];
            $featured = $track['featured'] ?? [];
            $composer = $track['composer'] ?? 'Irene Zin Mar Myint';
            $lyricist = $track['lyricist'] ?? $composer;
            $vocals = $track['vocals'] ?? array_merge(['Irene Zin Mar Myint'], $featured);

            $songValues = [
                'album_id' => $albumId,
                'artist_id' => $artist->id,
                'title' => $track['title'],
                'version' => $track['version'] ?? null,
                'track_number' => $index + 1,
                'duration' => $track['duration'],
                'isrc_code' => $track['isrc'],
                'request_new_isrc' => false,
                'explicit' => false,
                'genre' => $release['genre'] ?? 'Pop',
                'primary_artists' => json_encode($primary, JSON_UNESCAPED_UNICODE),
                'composers' => json_encode([$composer], JSON_UNESCAPED_UNICODE),
                'lyricist' => json_encode([$lyricist], JSON_UNESCAPED_UNICODE),
                'producers' => json_encode(['Irene Zin Mar Myint'], JSON_UNESCAPED_UNICODE),
                'vocals' => json_encode($vocals, JSON_UNESCAPED_UNICODE),
                'featuring' => json_encode($featured, JSON_UNESCAPED_UNICODE),
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
    }

    private function releases(): array
    {
        $single = fn (string $title, string $upc, string $date, string $approved, string $isrc, string $duration, string $composer, string $cover, string $reference, string $sourceId, array $extra = []) => [
            'title' => $title, 'upc' => $upc, 'date' => $date, 'approved' => $approved,
            'type' => 'single', 'cover' => $cover, 'reference' => $reference, 'source_id' => $sourceId,
            'genre' => $extra['genre'] ?? 'Pop',
            'tracks' => [[
                'title' => $extra['track_title'] ?? preg_replace('/ \((Deluxe|New Version)\)$/', '', $title),
                'version' => $extra['version'] ?? (str_contains($title, '(Deluxe)') ? 'Deluxe' : (str_contains($title, '(New Version)') ? 'New Version' : null)),
                'isrc' => $isrc, 'duration' => $duration, 'composer' => $composer,
                'lyricist' => $extra['lyricist'] ?? $composer,
                'primary' => $extra['primary'] ?? ['Irene Zin Mar Myint'],
                'featured' => $extra['featured'] ?? [],
                'vocals' => $extra['vocals'] ?? null,
            ]],
        ];

        return [
            $single('Min Ma Thi Thaw (Deluxe)', '8447536112968', '2025-06-17', '2025-06-23', 'QZXLZ2526055', '4:03', 'Aung Myint Myat', 'min-ma-thi-thaw-deluxe', 'TELEMUSIC2500046', '2063763'),
            $single('Shin Lyat Nae (Deluxe)', '8447536112999', '2025-06-17', '2025-06-23', 'QZXLZ2526070', '3:32', 'Kar Gyi', 'shin-lyat-nae-deluxe', 'TELEMUSIC2500049', '2063777'),
            $single('Daw Tha Sait Ka Lay 360 Minutes (Deluxe)', '8447536113026', '2025-06-18', '2025-06-23', 'QZXLZ2526072', '4:57', 'Min Zaw', 'daw-tha-sait-ka-lay-360-minutes-deluxe', 'TELEMUSIC2500051', '2063814'),
            $single('Kaung Kin Htae Ka Myit Ta Sinn (Deluxe)', '8447536113071', '2025-06-18', '2025-06-23', 'QZXLZ2526073', '5:34', 'Myint Moe Aung', 'kaung-kin-htae-ka-myit-ta-sinn-deluxe', 'TELEMUSIC2500052', '2063818'),
            $single('Kwint Ma Hloot Chin Pay Mae (Deluxe)', '8447536113088', '2025-06-18', '2025-06-23', 'QZXLZ2526074', '4:27', 'Aung Myint Myat', 'kwint-ma-hloot-chin-pay-mae-deluxe', 'TELEMUSIC2500053', '2063832'),
            $single('Nway Ta Khu Yae A Lwan Dan Yar (Deluxe)', '8447536113132', '2025-06-18', '2025-06-23', 'QZXLZ2526078', '4:00', 'Zayer Nway', 'nway-ta-khu-yae-a-lwan-dan-yar-deluxe', 'TELEMUSIC2500057', '2063844'),
            $single('Mone Khae Thi (Deluxe)', '8447536113101', '2025-06-18', '2025-06-23', 'QZXLZ2526076', '3:36', 'Shine Wai Yan', 'mone-khae-thi-deluxe', 'TELEMUSIC2500055', '2063854'),
            $single('Moe Sat Tin Lay (Deluxe)', '8447536112982', '2025-06-18', '2025-06-23', 'QZXLZ2526069', '5:47', 'Sue', 'moe-sat-tin-lay-deluxe', 'TELEMUSIC2500048', '2063867'),
            $single('Htar Wa Ya A Sone Hti Myaw Lint Chin (Deluxe)', '8447536113095', '2025-06-18', '2025-06-23', 'QZXLZ2526075', '3:54', 'Mary Thway', 'htar-wa-ya-a-sone-hti-myaw-lint-chin-deluxe', 'TELEMUSIC2500054', '2063881', ['genre' => 'Spiritual/Gospel']),
            $single('Taung Su (Deluxe)', '8447536113118', '2025-06-19', '2025-06-23', 'QZXLZ2526077', '4:11', 'Clone', 'taung-su-deluxe', 'TELEMUSIC2500056', '2066607'),
            $single('Moe Sat Tin Lay (New Version)', '8447536113149', '2025-06-19', '2025-06-23', 'QZXLZ2526079', '5:15', 'Sue', 'moe-sat-tin-lay-new-version', 'TELEMUSIC2500058', '2066614'),
            $single('Tha Mee Yae May May (Deluxe)', '8447536113163', '2025-06-19', '2025-06-23', 'QZXLZ2526081', '4:22', 'Kaung Kaung', 'tha-mee-yae-may-may-deluxe', 'TELEMUSIC2500060', '2066630'),
            $single('Christmas Song (Deluxe)', '8447536113170', '2025-06-19', '2025-06-23', 'QZXLZ2526082', '3:45', 'Irene Zin Mar Myint', 'christmas-song-deluxe', 'TELEMUSIC2500061', '2066650'),
            $single('A Tate Ka Akyaung', '8447536515486', '2025-09-20', '2025-10-02', 'QZXLZ2546702', '4:39', 'Phoe Tu', 'a-tate-ka-akyaung', 'TELEMUSIC2500098', '2066658'),
            $single('Noon (Deluxe)', '8447536113156', '2025-06-19', '2025-06-23', 'QZXLZ2526080', '3:49', 'Nor Kyone Lyan', 'noon-deluxe', 'TELEMUSIC2500059', '2066662'),
            $this->nanYoePaingShin(),
            $this->yeinLite(),
            $this->chitLoh(),
            $single('Yin Htae Mar Bae Htar Pa Mal', '8447536782307', '2025-12-05', '2025-12-05', 'QZXLZ2559550', '3:46', 'A LYNN', 'yin-htae-mar-bae-htar-pa-mal', 'TELEMUSIC2500116', '2281415'),
            $single('Remember Me', '8447536788682', '2025-12-12', '2025-12-09', 'QZXLZ2559728', '3:56', 'Aye Lwin', 'remember-me', 'TELEMUSIC2500119', '2293433'),
            $single('The End', '8447536852185', '2025-12-22', '2025-12-29', 'QZXLZ2563430', '4:29', 'Kar Gyi', 'the-end', 'TELEMUSIC2500121', '2318243'),
            $single('Ma Kyar Khin Pyan Sone Twae Mal', '8447536925414', '2026-01-10', '2026-01-16', 'QZNW72601715', '4:23', 'Myint Moe Aung', 'ma-kyar-khin-pyan-sone-twae-mal', 'TELEMUSIC2600124', '2346859'),
            $single('Na Lone Thar A Yin A Nee', '8447721061279', '2026-02-10', '2026-02-12', 'QZNW72607769', '3:29', 'Phoe Tu', 'na-lone-thar-a-yin-a-nee', 'TELEMUSIC2600126', '2390836'),
            $single('Swal Taw Ywat', '8447721057852', '2026-02-13', '2026-02-11', 'QZNW72607662', '4:15', 'Lynn Htet', 'swal-taw-ywat', 'TELEMUSIC2600125', '2396339'),
            $single('Nyo Mya', '8447721148864', '2026-03-07', '2026-03-05', 'QZNW72613634', '3:33', 'Yu Wa Mg Mg Lay', 'nyo-mya', 'TELEMUSIC2600128', '2434173'),
            $single('Yay Tway So Kon P', '8447721163065', '2026-03-10', '2026-03-09', 'QZNW72614085', '3:22', 'U Hla Myint', 'yay-tway-so-kon-p', 'TELEMUSIC2600130', '2441450'),
            $single('Chan Par Tal', '8447721177802', '2026-03-13', '2026-03-11', 'QZNW72614563', '5:02', 'Yay Nant Thar Soe Hlaing', 'chan-par-tal', 'TELEMUSIC2600132', '2444469', ['primary' => ['Irene Zin Mar Myint', 'Zaw Paing'], 'vocals' => ['Irene Zin Mar Myint', 'Zaw Paing']]),
            $single('Nit Thit Chit Oo', '8447721177819', '2026-03-16', '2026-03-11', 'QZNW72614564', '4:42', 'Gita Lulin Mg Ko Ko', 'nit-thit-chit-oo', 'TELEMUSIC2600133', '2444475', ['primary' => ['Irene Zin Mar Myint', 'Si Thu Lwin'], 'vocals' => ['Irene Zin Mar Myint', 'Si Thu Lwin']]),
            $single('Bal Lo Lu Gyi Lae', '8447721227996', '2026-03-19', '2026-03-21', 'QZNW72616586', '4:39', 'Zay Yar Min', 'bal-lo-lu-gyi-lae', 'TELEMUSIC2600138', '2457775'),
            $single('Nit Thit Mingalar', '8447721228009', '2026-03-22', '2026-03-21', 'QZNW72616587', '3:10', 'Zay Yar Min', 'nit-thit-mingalar', 'TELEMUSIC2600139', '2457788'),
            $single('Thingyan Moe', '8447721243057', '2026-03-26', '2026-03-25', 'QZNW72617113', '4:08', 'Zay Yar Min', 'thingyan-moe', 'TELEMUSIC2600140', '2464076'),
            $single('Ta Nit Nwan Ta Nit Saann', '8447721476820', '2026-03-28', '2026-03-27', 'QZNW72628395', '4:25', 'Zay Yar Min', 'ta-nit-nwan-ta-nit-saann', 'TELEMUSIC2600149', '2466156'),
            $single('Nway Ma', '8447721478916', '2026-04-01', '2026-03-30', 'QZNW72628432', '3:21', 'Nyein Htet', 'nway-ma', 'TELEMUSIC2600150', '2469859', ['featured' => ['Si Thu Lwin', 'Ga Yay Han', 'Zaw Paing', 'Phoe Kar']]),
            $single('Dan Yar Myar Nae', '8447721398719', '2026-05-01', '2026-05-04', 'QZNW72624623', '3:20', 'Irene Zin Mar Myint', 'dan-yar-myar-nae', 'TELEMUSIC2600147', '2506776'),
            $single('Letter To Friend', '8447721398726', '2026-05-02', '2026-05-04', 'QZNW72624624', '5:16', 'Irene Zin Mar Myint', 'letter-to-friend', 'TELEMUSIC2600148', '2506787'),
            $single('Radio', '8447721398702', '2026-05-08', '2026-05-04', 'QZNW72624622', '3:51', 'Irene Zin Mar Myint', 'radio', 'TELEMUSIC2600146', '2510760'),
            $single('If With You', '8447721558366', '2026-05-29', '2026-06-03', 'QZNW72630553', '2:57', 'Irene Zin Mar Myint', 'if-with-you', 'TELEMUSIC2600152', '2541830', ['featured' => ['Tun Tun'], 'vocals' => ['Irene Zin Mar Myint', 'Tun Tun']]),
        ];
    }

    private function nanYoePaingShin(): array
    {
        $titles = ['Kabar Law Ka A Twat', 'Nouk Sone Htwat Thet Tai', 'Sate Tu Ko Tu A Chit Htet Tu', 'Nan Yoe Paing Shin', 'A Yin A Taing Kyaung Taw Gyi', 'Su Lat', 'Twal Lat Khaing Myat', 'Chit Thu Birthday', 'Ma Shi Ma Pyit', 'Hey!... Love'];
        $durations = ['4:27', '4:23', '3:22', '4:30', '3:30', '4:11', '4:31', '3:59', '4:06', '3:07'];

        return $this->album('Nan Yoe Paing Shin', '8447536437566', '2025-09-19', '2025-09-18', 'nan-yoe-paing-shin', 'TELEMUSIC2500079', '2180876', array_map(fn ($title, $i) => [
            'title' => $title, 'isrc' => 'QZXLZ254'.(2785 + $i), 'duration' => $durations[$i],
            'composer' => 'KO LU', 'featured' => ['Kaung Kaung'],
        ], $titles, array_keys($titles)));
    }

    private function yeinLite(): array
    {
        $rows = [
            ['Dan Yar Myar Nae', '3:22', 'May Thaw'], ['Bal Lo A Chit Myo Nae', '3:42', 'Irene Zin Mar Myint'],
            ['Pyar Hlae Ma Kyi Bu', '5:02', 'Kaung Kaung'], ['Yein Lite', '3:07', 'Shwe Jaw Jaw'],
            ['Lwan Nay Loh', '3:58', 'Gan Dee'], ['A Sone Mae Eain Mat', '4:03', 'Ko Myo PTL'],
            ['Yar Za Win Twin Mae Chit Chin', '2:56', 'Kaung Kaung', ['Kaung Kaung']], ['Thet Sone Tine', '3:29', 'Thomas'],
            ['Min Nae A Tu', '3:38', 'Kane', ['Myint Myat']], ['Ta Zot Htoe', '2:56', 'Saw Balu'],
            ['A Phae Khan', '3:43', 'Phoe Tu', [], 'Irene Zin Mar Myint'], ['Kyein Lite Own Lain Lite Own', '3:02', 'Aung Naing San'],
            ['Tha Mee Yae May May', '4:33', 'Kaung Kaung'], ['A Pyar Yaung Tay Than', '4:43', 'Myint Moe Aung'],
        ];

        return $this->album('Yein Lite', '8447536472253', '2025-09-19', '2025-09-25', 'yein-lite', 'TELEMUSIC2500081', '2180931', array_map(fn ($row, $i) => [
            'title' => $row[0], 'duration' => $row[1], 'composer' => $row[2], 'lyricist' => $row[4] ?? $row[2],
            'featured' => $row[3] ?? [], 'isrc' => 'QZXLZ254'.(5029 + $i),
        ], $rows, array_keys($rows)));
    }

    private function chitLoh(): array
    {
        $rows = [
            ['Sount Myaw Hlat Par Chit Thu', '3:52', 'Kaung Kaung'], ['Lat Twal Htar Mal', '3:31', 'Sang Pi'],
            ['Di Mya', '3:45', 'Juno'], ['Kyaw Nyar Ma Win Nae', '3:46', 'Myint Moe Aung'],
            ['Htet Tu Nyi Tae A Chit', '3:53', 'Aww Nay Thein'], ['A Chit Yae Ka Bar', '3:08', 'Kaung Kaung'],
            ['Ta Nay Nay', '3:48', 'Leo Bo Bo'], ['Free Girl', '3:44', 'Kaung Kaung'],
            ['I Love You', '4:19', 'Myint Moe Aung'], ['Love', '3:17', 'Gan Dee', ['Kaung Kaung']],
            ['A Mat Ta Ya Lann Ka Lay', '3:23', 'Shwe Jaw Jaw'], ['Sone Yay', '3:22', 'Saw Win Lwin'],
            ['Sate Kue Nae Yuu', '4:06', 'Jay Jue'],
        ];

        return $this->album('Chit Loh', '8447536615889', '2025-09-20', '2025-10-24', 'chit-loh', 'TELEMUSIC2500106', '2181042', array_map(fn ($row, $i) => [
            'title' => $row[0], 'duration' => $row[1], 'composer' => $row[2], 'lyricist' => 'Irene Zin Mar Myint',
            'featured' => $row[3] ?? [], 'isrc' => 'QZXLZ255'.sprintf('%04d', 943 + $i),
        ], $rows, array_keys($rows)));
    }

    private function album(string $title, string $upc, string $date, string $approved, string $cover, string $reference, string $sourceId, array $tracks): array
    {
        return compact('title', 'upc', 'date', 'approved', 'cover', 'reference', 'tracks') + [
            'type' => 'album', 'genre' => 'Pop', 'source_id' => $sourceId,
        ];
    }

    public function down(): void
    {
        $artistId = DB::table('users')
            ->join('artists', 'artists.user_id', '=', 'users.id')
            ->where('users.email', self::EMAIL)
            ->value('artists.id');

        if (! $artistId) {
            return;
        }

        $upcs = array_column($this->releases(), 'upc');
        DB::table('albums')->where('artist_id', $artistId)->whereIn('upc_code', $upcs)->delete();
    }
};
