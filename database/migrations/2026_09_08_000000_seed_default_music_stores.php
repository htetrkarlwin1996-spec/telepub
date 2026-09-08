<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $stores = [
            ['Spotify', 'spotify', 'https://artists.spotify.com', 'https://storage.googleapis.com/pr-newsroom-wp/1/2018/11/Spotify_Logo_RGB_Green.png'],
            ['Apple Music', 'apple-music', 'https://music.apple.com', 'https://developer.apple.com/assets/elements/icons/apple-music/apple-music-96x96.png'],
            ['iTunes', 'itunes', 'https://itunes.apple.com', 'https://developer.apple.com/assets/elements/icons/itunes/itunes-96x96.png'],
            ['Instagram & Facebook (Meta)', 'instagram-facebook-meta', 'https://meta.com', 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/Instagram_icon.png/64px-Instagram_icon.png'],
            ['TikTok & ByteDance', 'tiktok', 'https://tiktok.com', 'https://sf16-sg.tiktokcdn.com/obj/eden-sg/uvhvbplo/tt-icon.png'],
            ['YouTube Music', 'youtube-music', 'https://music.youtube.com', 'https://www.gstatic.com/youtube/img/branding/favicon/favicon_32x32.png'],
            ['YouTube Audio Content ID', 'youtube-audio-content-id', 'https://www.youtube.com', 'https://www.gstatic.com/youtube/img/branding/favicon/favicon_32x32.png'],
            ['YouTube Art Tracks', 'youtube-art-tracks', 'https://www.youtube.com', 'https://www.gstatic.com/youtube/img/branding/favicon/favicon_32x32.png'],
            ['Amazon Music', 'amazon', 'https://music.amazon.com', 'https://images-na.ssl-images-amazon.com/images/G/01/AmazonMusic/AmazonMusicLogoBlue_32x32.png'],
            ['Pandora', 'pandora', 'https://pandora.com', 'https://www.pandora.com/favicon.ico'],
            ['Deezer', 'deezer', 'https://deezer.com', 'https://e-cdns-files.dzcdn.net/cache/images/common/favicon.ico'],
            ['TIDAL', 'tidal', 'https://tidal.com', 'https://tidal.com/favicon.ico'],
            ['iHeartRadio', 'iheartradio', 'https://iheart.com', 'https://www.iheart.com/favicon.ico'],
            ['Qobuz', 'qobuz', 'https://qobuz.com', 'https://www.qobuz.com/favicon.ico'],
            ['JioSaavn', 'saavn', 'https://jiosaavn.com', 'https://www.jiosaavn.com/favicon.ico'],
            ['Boomplay', 'boomplay', 'https://boomplay.com', 'https://www.boomplay.com/favicon.ico'],
            ['Anghami', 'anghami', 'https://anghami.com', 'https://www.anghami.com/favicon.ico'],
            ['NetEase Cloud Music', 'netease', 'https://music.163.com', 'https://music.163.com/favicon.ico'],
            ['Tencent Music', 'tencent', 'https://tencentmusic.com', 'https://imgcache.qq.com/favicon.ico'],
            ['Claro Música', 'claro-musica', 'https://claromusica.com', 'https://www.claromusica.com/favicon.ico'],
            ['Joox', 'joox', 'https://joox.com', 'https://www.joox.com/favicon.ico'],
            ['Kuack Media', 'kuack-media', 'https://kuackmedia.com', 'https://www.kuackmedia.com/favicon.ico'],
            ['Adaptr', 'adaptr', 'https://adaptr.com', 'https://adaptr.com/favicon.ico'],
            ['FLO Music', 'flo', 'https://flo.zikimove.com', 'https://flo.zikimove.com/favicon.ico'],
            ['MediaNet', 'medianet', 'https://medianet.xyz', 'https://medianet.xyz/favicon.ico'],
            ['Snapchat', 'snapchat', 'https://snapchat.com', 'https://www.snapchat.com/favicon.ico'],
            ['Roblox (Beta)', 'roblox', 'https://roblox.com', 'https://www.roblox.com/favicon.ico'],
        ];

        DB::table('music_stores')->insertOrIgnore(array_map(
            fn (array $store) => [
                'name' => $store[0],
                'slug' => $store[1],
                'url' => $store[2],
                'logo' => $store[3],
                'description' => $store[0].' music distribution platform',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $stores,
        ));
    }

    public function down(): void
    {
        // Default stores are retained because releases may reference them.
    }
};
