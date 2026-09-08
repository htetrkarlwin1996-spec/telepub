<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Artist;
use App\Models\Album;
use App\Models\Song;
use App\Models\MusicStore;
use App\Models\Distribution;
use App\Models\Royalty;
use App\Models\Analytics;
use App\Models\Invoice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== CREATE / PRESERVE ACCOUNTS =====
        // AccountSeeder uses firstOrCreate so it's safe to run repeatedly
        $this->call(AccountSeeder::class);

        // Look up accounts (they exist now thanks to AccountSeeder)
        $admin = User::where('email', 'admin@example.com')->first();
        $artist1User = User::where('email', 'luna@example.com')->first();
        $artist2User = User::where('email', 'phoenix@example.com')->first();
        $artist1 = Artist::where('user_id', $artist1User->id)->first();
        $artist2 = Artist::where('user_id', $artist2User->id)->first();

        // ===== CREATE DISTROKID MUSIC STORES WITH LOGOS =====
        $storeLogos = [
            'spotify' => 'https://storage.googleapis.com/pr-newsroom-wp/1/2018/11/Spotify_Logo_RGB_Green.png',
            'apple-music' => 'https://developer.apple.com/assets/elements/icons/apple-music/apple-music-96x96.png',
            'itunes' => 'https://developer.apple.com/assets/elements/icons/itunes/itunes-96x96.png',
            'instagram-facebook-meta' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/Instagram_icon.png/64px-Instagram_icon.png',
            'tiktok' => 'https://sf16-sg.tiktokcdn.com/obj/eden-sg/uvhvbplo/tt-icon.png',
            'youtube-music' => 'https://www.gstatic.com/youtube/img/branding/favicon/favicon_32x32.png',
            'amazon' => 'https://images-na.ssl-images-amazon.com/images/G/01/AmazonMusic/AmazonMusicLogoBlue_32x32.png',
            'pandora' => 'https://www.pandora.com/favicon.ico',
            'deezer' => 'https://e-cdns-files.dzcdn.net/cache/images/common/favicon.ico',
            'tidal' => 'https://tidal.com/favicon.ico',
            'iheartradio' => 'https://www.iheart.com/favicon.ico',
            'qobuz' => 'https://www.qobuz.com/favicon.ico',
            'saavn' => 'https://www.jiosaavn.com/favicon.ico',
            'boomplay' => 'https://www.boomplay.com/favicon.ico',
            'anghami' => 'https://www.anghami.com/favicon.ico',
            'netease' => 'https://music.163.com/favicon.ico',
            'tencent' => 'https://imgcache.qq.com/favicon.ico',
            'claro-musica' => 'https://www.claromusica.com/favicon.ico',
            'joox' => 'https://www.joox.com/favicon.ico',
            'kuack-media' => 'https://www.kuackmedia.com/favicon.ico',
            'adaptr' => 'https://adaptr.com/favicon.ico',
            'flo' => 'https://flo.zikimove.com/favicon.ico',
            'medianet' => 'https://medianet.xyz/favicon.ico',
            'snapchat' => 'https://www.snapchat.com/favicon.ico',
            'roblox' => 'https://www.roblox.com/favicon.ico',
        ];

        $stores = [
            ['name' => 'Spotify', 'slug' => 'spotify', 'description' => 'World\'s largest music streaming service with over 500 million users', 'url' => 'https://artists.spotify.com', 'is_active' => true],
            ['name' => 'Apple Music', 'slug' => 'apple-music', 'description' => 'Apple\'s premium music streaming service with lossless audio', 'url' => 'https://music.apple.com', 'is_active' => true],
            ['name' => 'iTunes', 'slug' => 'itunes', 'description' => 'Apple\'s digital media store for purchasing music', 'url' => 'https://itunes.apple.com', 'is_active' => true],
            ['name' => 'Instagram & Facebook (Meta)', 'slug' => 'instagram-facebook-meta', 'description' => 'Music distribution to Instagram and Facebook platforms', 'url' => 'https://meta.com', 'is_active' => true],
            ['name' => 'TikTok & ByteDance', 'slug' => 'tiktok', 'description' => 'Music distribution to TikTok\'s short-form video platform and ByteDance network', 'url' => 'https://tiktok.com', 'is_active' => true],
            ['name' => 'YouTube Music', 'slug' => 'youtube-music', 'description' => 'YouTube\'s dedicated music streaming platform', 'url' => 'https://music.youtube.com', 'is_active' => true],
            ['name' => 'Amazon Music', 'slug' => 'amazon', 'description' => 'Amazon\'s music streaming platform integrated with Prime', 'url' => 'https://music.amazon.com', 'is_active' => true],
            ['name' => 'Pandora', 'slug' => 'pandora', 'description' => 'US-based personalized internet radio and streaming service', 'url' => 'https://pandora.com', 'is_active' => true],
            ['name' => 'Deezer', 'slug' => 'deezer', 'description' => 'Global music streaming service available in 180+ countries', 'url' => 'https://deezer.com', 'is_active' => true],
            ['name' => 'Tidal', 'slug' => 'tidal', 'description' => 'Hi-fi music streaming with high-fidelity audio quality', 'url' => 'https://tidal.com', 'is_active' => true],
            ['name' => 'iHeartRadio', 'slug' => 'iheartradio', 'description' => 'US-based internet radio and streaming platform', 'url' => 'https://iheart.com', 'is_active' => true],
            ['name' => 'Qobuz', 'slug' => 'qobuz', 'description' => 'High-resolution audio streaming service with studio-quality sound', 'url' => 'https://qobuz.com', 'is_active' => true],
            ['name' => 'JioSaavn', 'slug' => 'saavn', 'description' => 'Indian music streaming service by Reliance Industries', 'url' => 'https://jiosaavn.com', 'is_active' => true],
            ['name' => 'Boomplay', 'slug' => 'boomplay', 'description' => 'Leading music streaming platform in Africa', 'url' => 'https://boomplay.com', 'is_active' => true],
            ['name' => 'Anghami', 'slug' => 'anghami', 'description' => 'Leading music streaming platform in the Middle East and North Africa', 'url' => 'https://anghami.com', 'is_active' => true],
            ['name' => 'NetEase Cloud Music', 'slug' => 'netease', 'description' => 'Chinese music streaming platform by NetEase', 'url' => 'https://music.163.com', 'is_active' => true],
            ['name' => 'Tencent Music', 'slug' => 'tencent', 'description' => 'Chinese online music entertainment platform (QQ Music, KuGou, Kuwo)', 'url' => 'https://tencentmusic.com', 'is_active' => true],
            ['name' => 'Claro Música', 'slug' => 'claro-musica', 'description' => 'Latin American music streaming service by Claro', 'url' => 'https://claromusica.com', 'is_active' => true],
            ['name' => 'Joox', 'slug' => 'joox', 'description' => 'Southeast Asian music streaming platform by Tencent', 'url' => 'https://joox.com', 'is_active' => true],
            ['name' => 'Kuack Media', 'slug' => 'kuack-media', 'description' => 'Latin American digital music distribution platform', 'url' => 'https://kuackmedia.com', 'is_active' => true],
            ['name' => 'Adaptr', 'slug' => 'adaptr', 'description' => 'White-label music streaming platform provider', 'url' => 'https://adaptr.com', 'is_active' => true],
            ['name' => 'FLO Music', 'slug' => 'flo', 'description' => 'South Korean AI-powered music streaming service', 'url' => 'https://flo.zikimove.com', 'is_active' => true],
            ['name' => 'MediaNet', 'slug' => 'medianet', 'description' => 'Global digital music distribution platform', 'url' => 'https://medianet.xyz', 'is_active' => true],
            ['name' => 'Snapchat', 'slug' => 'snapchat', 'description' => 'Music distribution to Snapchat\'s social media platform', 'url' => 'https://snapchat.com', 'is_active' => true],
            ['name' => 'Roblox (Beta)', 'slug' => 'roblox', 'description' => 'Music distribution to Roblox metaverse gaming platform', 'url' => 'https://roblox.com', 'is_active' => true],
        ];

        foreach ($stores as $storeData) {
            $logo = $storeLogos[$storeData['slug']] ?? null;
            MusicStore::firstOrCreate(
                ['slug' => $storeData['slug']],
                array_merge($storeData, ['logo' => $logo])
            );
        }

        // ===== SAMPLE DATA: Only create if no albums exist yet =====
        if (Album::count() > 0) {
            $this->command->info('Sample albums already exist — skipping sample data.');
            $this->command->info('Accounts preserved!');
            $this->command->info('Admin: admin@example.com / password');
            $this->command->info('Artist 1: luna@example.com / password (Luna Star)');
            $this->command->info('Artist 2: phoenix@example.com / password (Phoenix Blaze)');
            $this->command->info('Total stores: ' . MusicStore::count() . ' (DistroKid stores)');
            return;
        }

        // Album 1 - Midnight Dreams
        $album1 = Album::create([
            'artist_id' => $artist1->id,
            'title' => 'Midnight Dreams',
            'slug' => 'midnight-dreams',
            'genre' => 'Pop',
            'label' => 'Star Records',
            'release_date' => '2026-01-15',
            'upc_code' => '123456789012',
            'status' => 'approved',
        ]);

        // Songs for Album 1
        $songs1 = [
            ['title' => 'Starlight', 'track_number' => 1, 'duration' => 234, 'isrc_code' => 'USABC0100001', 'genre' => 'Pop', 'composers' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => ''], ['name' => 'Max Producer', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'producers' => [['name' => 'Max Producer', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'primary_artists' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'status' => 'approved'],
            ['title' => 'Midnight Rain', 'track_number' => 2, 'duration' => 198, 'isrc_code' => 'USABC0100002', 'genre' => 'Pop', 'composers' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'producers' => [['name' => 'Max Producer', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'primary_artists' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'status' => 'approved'],
            ['title' => 'Dancing Shadows', 'track_number' => 3, 'duration' => 215, 'isrc_code' => 'USABC0100003', 'genre' => 'Pop', 'composers' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => ''], ['name' => 'DJ Night', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'producers' => [['name' => 'DJ Night', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'featuring' => [['name' => 'DJ Night', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'primary_artists' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'status' => 'approved'],
            ['title' => 'Crystal Clear', 'track_number' => 4, 'duration' => 247, 'isrc_code' => 'USABC0100004', 'genre' => 'Pop Ballad', 'composers' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'producers' => [['name' => 'Max Producer', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'primary_artists' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'status' => 'approved'],
        ];

        $createdSongs1 = [];
        foreach ($songs1 as $songData) {
            $createdSongs1[] = Song::create(array_merge($songData, [
                'album_id' => $album1->id,
                'artist_id' => $artist1->id,
                'language' => 'English',
                'explicit' => false,
            ]));
        }

        // Album 2 - Summer Vibes
        $album2 = Album::create([
            'artist_id' => $artist1->id,
            'title' => 'Summer Vibes',
            'slug' => 'summer-vibes',
            'genre' => 'Pop',
            'label' => 'Star Records',
            'release_date' => '2026-06-01',
            'upc_code' => '123456789013',
            'status' => 'approved',
        ]);

        $songs2 = [
            ['title' => 'Beach Party', 'track_number' => 1, 'duration' => 221, 'isrc_code' => 'USABC0100005', 'genre' => 'Pop Dance', 'composers' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => ''], ['name' => 'Beach Boy', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'producers' => [['name' => 'Beach Boy', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'primary_artists' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'status' => 'approved'],
            ['title' => 'Sunset Glow', 'track_number' => 2, 'duration' => 203, 'isrc_code' => 'USABC0100006', 'genre' => 'Pop', 'composers' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'producers' => [['name' => 'Max Producer', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'primary_artists' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'status' => 'approved'],
            ['title' => 'Ocean Waves', 'track_number' => 3, 'duration' => 256, 'isrc_code' => 'USABC0100007', 'genre' => 'Pop', 'composers' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => ''], ['name' => 'Wave Runner', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'producers' => [['name' => 'Wave Runner', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'featuring' => [['name' => 'Wave Runner', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'primary_artists' => [['name' => 'Luna Star', 'spotify_url' => 'https://open.spotify.com/artist/1x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'status' => 'approved'],
        ];

        foreach ($songs2 as $songData) {
            Song::create(array_merge($songData, [
                'album_id' => $album2->id,
                'artist_id' => $artist1->id,
                'language' => 'English',
                'explicit' => false,
            ]));
        }
        $allArtist1Songs = Song::where('artist_id', $artist1->id)->get();

        // Album 3 - Rise from Ashes
        $album3 = Album::create([
            'artist_id' => $artist2->id,
            'title' => 'Rise from Ashes',
            'slug' => 'rise-from-ashes',
            'genre' => 'Hip-Hop',
            'label' => 'Blaze Records',
            'release_date' => '2026-03-20',
            'upc_code' => '987654321098',
            'status' => 'approved',
        ]);

        $songs3 = [
            ['title' => 'Fire Starter', 'track_number' => 1, 'duration' => 212, 'isrc_code' => 'USABC0200001', 'genre' => 'Hip-Hop', 'composers' => [['name' => 'Phoenix Blaze', 'spotify_url' => 'https://open.spotify.com/artist/2x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'producers' => [['name' => 'Phoenix Blaze', 'spotify_url' => 'https://open.spotify.com/artist/2x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'primary_artists' => [['name' => 'Phoenix Blaze', 'spotify_url' => 'https://open.spotify.com/artist/2x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'explicit' => true, 'status' => 'approved'],
            ['title' => 'Street Kings', 'track_number' => 2, 'duration' => 198, 'isrc_code' => 'USABC0200002', 'genre' => 'Hip-Hop', 'composers' => [['name' => 'Phoenix Blaze', 'spotify_url' => 'https://open.spotify.com/artist/2x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => ''], ['name' => 'Beat Master', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'producers' => [['name' => 'Beat Master', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'featuring' => [['name' => 'Beat Master', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'primary_artists' => [['name' => 'Phoenix Blaze', 'spotify_url' => 'https://open.spotify.com/artist/2x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'explicit' => true, 'status' => 'approved'],
            ['title' => 'Crown Heavy', 'track_number' => 3, 'duration' => 234, 'isrc_code' => 'USABC0200003', 'genre' => 'Hip-Hop', 'composers' => [['name' => 'Phoenix Blaze', 'spotify_url' => 'https://open.spotify.com/artist/2x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'producers' => [['name' => 'Phoenix Blaze', 'spotify_url' => 'https://open.spotify.com/artist/2x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'primary_artists' => [['name' => 'Phoenix Blaze', 'spotify_url' => 'https://open.spotify.com/artist/2x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'explicit' => false, 'status' => 'approved'],
            ['title' => 'Rise Up', 'track_number' => 4, 'duration' => 267, 'isrc_code' => 'USABC0200004', 'genre' => 'Hip-Hop', 'composers' => [['name' => 'Phoenix Blaze', 'spotify_url' => 'https://open.spotify.com/artist/2x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => ''], ['name' => 'DJ Flame', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'producers' => [['name' => 'DJ Flame', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'featuring' => [['name' => 'DJ Flame', 'spotify_url' => '', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'primary_artists' => [['name' => 'Phoenix Blaze', 'spotify_url' => 'https://open.spotify.com/artist/2x', 'apple_music_url' => '', 'youtube_url' => '', 'tidal_url' => '']], 'explicit' => false, 'status' => 'approved'],
        ];

        foreach ($songs3 as $songData) {
            Song::create(array_merge($songData, [
                'album_id' => $album3->id,
                'artist_id' => $artist2->id,
                'language' => 'English',
            ]));
        }
        $allArtist2Songs = Song::where('artist_id', $artist2->id)->get();

        // ===== CREATE DISTRIBUTIONS =====
        $storeIds = MusicStore::pluck('id')->toArray();

        // Distribute Artist 1 songs to various stores
        foreach ($allArtist1Songs as $index => $song) {
            Distribution::create([
                'song_id' => $song->id,
                'album_id' => $song->album_id,
                'store_id' => $storeIds[$index % count($storeIds)],
                'artist_id' => $artist1->id,
                'status' => $index < 3 ? 'live' : 'submitted',
                'distribution_fee' => 0.99,
                'submitted_at' => now()->subDays(30 - $index * 5),
                'live_at' => $index < 3 ? now()->subDays(25 - $index * 5) : null,
            ]);
        }

        // Distribute Artist 2 songs
        foreach ($allArtist2Songs as $index => $song) {
            Distribution::create([
                'song_id' => $song->id,
                'album_id' => $song->album_id,
                'store_id' => $storeIds[($index + 2) % count($storeIds)],
                'artist_id' => $artist2->id,
                'status' => $index < 2 ? 'live' : ($index < 3 ? 'approved' : 'submitted'),
                'distribution_fee' => 0.99,
                'submitted_at' => now()->subDays(20 - $index * 4),
                'approved_at' => $index < 3 ? now()->subDays(15 - $index * 4) : null,
                'live_at' => $index < 2 ? now()->subDays(10 - $index * 4) : null,
            ]);
        }

        // ===== CREATE ROYALTIES =====
        $months = [4, 5, 6];
        foreach ($months as $monthIdx => $month) {
            foreach ($allArtist1Songs as $songIdx => $song) {
                $storeKey = ($songIdx + $monthIdx) % count($storeIds);
                $streams = rand(5000, 50000);
                $amount = round($streams * 0.004, 2);

                Royalty::create([
                    'artist_id' => $artist1->id,
                    'song_id' => $song->id,
                    'album_id' => $song->album_id,
                    'store_id' => $storeIds[$storeKey],
                    'month' => $month,
                    'year' => 2026,
                    'amount' => $amount,
                    'currency' => 'USD',
                    'streams' => $streams,
                    'entered_by' => $admin->id,
                ]);
            }
        }

        foreach ($months as $monthIdx => $month) {
            foreach ($allArtist2Songs as $songIdx => $song) {
                $storeKey = ($songIdx + $monthIdx + 1) % count($storeIds);
                $streams = rand(10000, 80000);
                $amount = round($streams * 0.004, 2);

                Royalty::create([
                    'artist_id' => $artist2->id,
                    'song_id' => $song->id,
                    'album_id' => $song->album_id,
                    'store_id' => $storeIds[$storeKey],
                    'month' => $month,
                    'year' => 2026,
                    'amount' => $amount,
                    'currency' => 'USD',
                    'streams' => $streams,
                    'entered_by' => $admin->id,
                ]);
            }
        }

        // ===== CREATE ANALYTICS =====
        foreach ($months as $month) {
            foreach ($allArtist1Songs as $songIdx => $song) {
                $storeKey = ($songIdx + $month) % count($storeIds);
                Analytics::create([
                    'artist_id' => $artist1->id,
                    'song_id' => $song->id,
                    'album_id' => $song->album_id,
                    'store_id' => $storeIds[$storeKey],
                    'month' => $month,
                    'year' => 2026,
                    'streams' => rand(5000, 50000),
                    'downloads' => rand(100, 2000),
                    'likes' => rand(200, 5000),
                    'playlist_adds' => rand(10, 500),
                    'revenue' => rand(20, 200),
                ]);
            }
        }

        foreach ($months as $month) {
            foreach ($allArtist2Songs as $songIdx => $song) {
                $storeKey = ($songIdx + $month + 2) % count($storeIds);
                Analytics::create([
                    'artist_id' => $artist2->id,
                    'song_id' => $song->id,
                    'album_id' => $song->album_id,
                    'store_id' => $storeIds[$storeKey],
                    'month' => $month,
                    'year' => 2026,
                    'streams' => rand(10000, 80000),
                    'downloads' => rand(200, 5000),
                    'likes' => rand(500, 10000),
                    'playlist_adds' => rand(20, 1000),
                    'revenue' => rand(50, 500),
                ]);
            }
        }

        // ===== CREATE INVOICES =====
        Invoice::create([
            'invoice_number' => 'INV-20260601-001',
            'artist_id' => $artist1->id,
            'amount' => 1250.00,
            'currency' => 'USD',
            'type' => 'royalty',
            'status' => 'paid',
            'issue_date' => '2026-06-01',
            'due_date' => '2026-06-30',
            'paid_date' => '2026-06-15',
            'description' => 'Monthly royalty payment for June 2026',
            'created_by' => $admin->id,
        ]);

        Invoice::create([
            'invoice_number' => 'INV-20260601-002',
            'artist_id' => $artist2->id,
            'amount' => 3200.00,
            'currency' => 'USD',
            'type' => 'royalty',
            'status' => 'sent',
            'issue_date' => '2026-06-01',
            'due_date' => '2026-06-30',
            'description' => 'Monthly royalty payment for June 2026',
            'created_by' => $admin->id,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin login: admin@example.com / password');
        $this->command->info('Artist 1 login: luna@example.com / password (Luna Star)');
        $this->command->info('Artist 2 login: phoenix@example.com / password (Phoenix Blaze)');
        $this->command->info('Total stores added: ' . count($stores) . ' (DistroKid stores)');
    }
}
