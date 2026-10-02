<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class LoginAnnouncement
{
    private const CACHE_KEY = 'login_announcement.settings';

    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'title' => '',
            'body' => '',
            'image_path' => null,
            'youtube_url' => '',
            'button_text' => '',
            'button_url' => '',
        ];
        if (! Schema::hasTable('app_settings')) {
            return $defaults;
        }

        return Cache::rememberForever(self::CACHE_KEY, function () use ($defaults) {
            $stored = json_decode((string) AppSetting::where('key', 'login_announcement')->value('value'), true);

            return array_merge($defaults, is_array($stored) ? $stored : []);
        });
    }

    public function forUser(?User $user): ?array
    {
        if (! $user || $user->isAdmin() || session()->has('impersonator_admin_id') || session()->get('login_announcement_shown')) {
            return null;
        }
        $settings = $this->settings();
        if (! $settings['enabled'] || ! $this->hasContent($settings)) {
            return null;
        }

        session()->put('login_announcement_shown', true);
        $settings['youtube_embed_url'] = $this->youtubeEmbedUrl($settings['youtube_url'] ?? '');

        return $settings;
    }

    public function youtubeEmbedUrl(?string $url): ?string
    {
        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $videoId = null;
        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $videoId = explode('/', $path)[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $videoId = $query['v'] ?? null;
            if (! $videoId && preg_match('~^(?:embed|shorts)/([A-Za-z0-9_-]{11})~', $path, $matches)) {
                $videoId = $matches[1];
            }
        }
        if (! is_string($videoId) || ! preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/'.$videoId.'?rel=0';
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function hasContent(array $settings): bool
    {
        return filled($settings['title'] ?? null) || filled($settings['body'] ?? null)
            || filled($settings['image_path'] ?? null) || filled($this->youtubeEmbedUrl($settings['youtube_url'] ?? ''));
    }
}
