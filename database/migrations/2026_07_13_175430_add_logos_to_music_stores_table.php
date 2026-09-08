<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The logo column already exists in music_stores.
        // This migration populates logo URLs for existing stores.
        $logos = [
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

        foreach ($logos as $slug => $url) {
            DB::table('music_stores')->where('slug', $slug)->update(['logo' => $url]);
        }
    }

    public function down(): void
    {
        DB::table('music_stores')->update(['logo' => null]);
    }
};
