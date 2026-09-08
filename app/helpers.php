<?php

if (!function_exists('formatCredit')) {
    /**
     * Format credit fields (primary_artists, featuring, composers, etc.)
     * that may contain arrays of strings or arrays of objects with links.
     *
     * @param mixed $field
     * @return string
     */
    function formatCredit($field): string
    {
        $data = $field ?? [];
        if (empty($data)) return '';
        if (is_array($data) && isset($data[0]) && is_string($data[0])) {
            return e(implode(', ', $data));
        }
        if (is_array($data)) {
            $names = array_map(function($entry) {
                if (is_string($entry)) return e($entry);
                $name = $entry['name'] ?? '';
                $links = [];
                if (!empty($entry['spotify_url'])) {
                    $links[] = '<a href="' . e($entry['spotify_url']) . '" target="_blank" class="underline hover:text-brand-600" title="Spotify"><svg class="w-3 h-3 inline" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.6 0 12 0zm5.5 17.3c-.2.3-.6.4-.9.2-2.5-1.5-5.6-1.9-9.3-1-.4.1-.7-.2-.7-.5 0-.4.2-.6.5-.7 4-.9 7.4-.5 10.1 1.1.3.2.4.6.3.9zm1.5-3.3c-.2.4-.7.5-1.1.3-2.8-1.7-7.1-2.2-10.4-1.2-.4.1-.9-.1-1-.5-.1-.4.1-.8.5-.9 3.7-1.1 8.4-.5 11.5 1.4.4.2.5.7.5 1zm.1-3.4c-.3.4-.8.6-1.2.3-3.4-2-8.5-2.5-12.6-1.4-.5.1-1-.1-1.1-.6-.1-.5.1-1 .6-1.1 4.7-1.2 10.3-.7 14.1 1.6.4.2.5.8.2 1.2z"/></svg></a>';
                }
                if (!empty($entry['apple_music_url'])) {
                    $links[] = '<a href="' . e($entry['apple_music_url']) . '" target="_blank" class="underline hover:text-brand-600" title="Apple Music"><svg class="w-3 h-3 inline" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.6 0 12 0zm5.5 17.3c-.2.3-.6.4-.9.2-2.5-1.5-5.6-1.9-9.3-1-.4.1-.7-.2-.7-.5 0-.4.2-.6.5-.7 4-.9 7.4-.5 10.1 1.1.3.2.4.6.3.9zm1.5-3.3c-.2.4-.7.5-1.1.3-2.8-1.7-7.1-2.2-10.4-1.2-.4.1-.9-.1-1-.5-.1-.4.1-.8.5-.9 3.7-1.1 8.4-.5 11.5 1.4.4.2.5.7.5 1zm.1-3.4c-.3.4-.8.6-1.2.3-3.4-2-8.5-2.5-12.6-1.4-.5.1-1-.1-1.1-.6-.1-.5.1-1 .6-1.1 4.7-1.2 10.3-.7 14.1 1.6.4.2.5.8.2 1.2z"/></svg></a>';
                }
                if (!empty($entry['youtube_url'])) {
                    $links[] = '<a href="' . e($entry['youtube_url']) . '" target="_blank" class="underline hover:text-brand-600" title="YouTube"><svg class="w-3 h-3 inline" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2c-.3-1-1-1.8-2-2-2-.5-10-.5-10-.5s-8 0-10 .5c-1 .2-1.8 1-2 2C-1 8.7-1 12-1 12s0 3.3.5 5.8c.3 1 1 1.8 2 2 2 .5 10 .5 10 .5s8 0 10-.5c1-.2 1.8-1 2-2 .5-2.5.5-5.8.5-5.8s0-3.3-.5-5.8zM9.5 15.5V8.5l6.5 3.5-6.5 3.5z"/></svg></a>';
                }
                if (!empty($entry['tidal_url'])) {
                    $links[] = '<a href="' . e($entry['tidal_url']) . '" target="_blank" class="underline hover:text-brand-600" title="Tidal"><svg class="w-3 h-3 inline" viewBox="0 0 24 24" fill="currentColor"><path d="M12.012 3.992L8.008 8.008 4.004 12l4.004 4.008 4.004 4.008 4.004-4.008L20.02 12l-4.004-4.008-4.004-4.004z"/></svg></a>';
                }
                return e($name) . ($links ? ' ' . implode(' ', $links) : '');
            }, $data);
            return implode(', ', $names);
        }
        return e($data);
    }
}

if (!function_exists('creditNames')) {
    /**
     * Extract just the names from a credit field for form input display.
     * Handles both array formats (structured objects with 'name' key, or plain strings)
     * and plain strings. Returns a comma-separated string of names only.
     *
     * @param mixed $field
     * @return string
     */
    function creditNames($field): string
    {
        $data = $field ?? [];
        if (empty($data)) return '';
        if (is_string($data)) return $data;
        if (is_array($data)) {
            $names = array_map(function($entry) {
                if (is_string($entry)) return $entry;
                return $entry['name'] ?? '';
            }, $data);
            return implode(', ', array_filter($names));
        }
        return (string) $data;
    }
}
