<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Artist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    /**
     * Seed the admin and demo artist accounts.
     * Run this separately with: php artisan db:seed --class=AccountSeeder
     * This won't delete existing data — it only creates missing accounts.
     */
    public function run(): void
    {
        // ===== CREATE TELEMUSIC ADMIN =====
        User::firstOrCreate(
            ['email' => 'info@telemusic.io'],
            [
                'name' => 'TeleMusic Admin',
                'password' => Hash::make('TeleMusic2026'),
                'role' => 'admin',
                'phone' => '+95-9-789654321',
                'bio' => 'TeleMusic platform administrator',
                'is_active' => true,
            ]
        );

        // ===== CREATE LEGACY ADMIN USER =====
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+1-555-0100',
                'bio' => 'Platform administrator',
                'is_active' => true,
            ]
        );

        // ===== CREATE mmstarlink532@gmail.com ARTIST =====
        $mmUser = User::firstOrCreate(
            ['email' => 'mmstarlink532@gmail.com'],
            [
                'name' => 'MM Star Link',
                'password' => Hash::make('123456789'),
                'role' => 'artist',
                'phone' => '+95-9-123456789',
                'bio' => 'Artist from Myanmar',
                'is_active' => true,
            ]
        );

        Artist::firstOrCreate(
            ['user_id' => $mmUser->id],
            [
                'artist_name' => 'MM Star Link',
                'genre' => 'Pop',
                'bio' => 'Myanmar-based music artist.',
                'country' => 'MM',
                'payment_email' => 'mmstarlink532@gmail.com',
                'paypal_email' => null,
                'total_earnings' => 0.00,
                'available_balance' => 0.00,
                'pending_balance' => 0.00,
            ]
        );

        // ===== CREATE SAMPLE ARTIST 1: Luna Star =====
        $artist1User = User::firstOrCreate(
            ['email' => 'luna@example.com'],
            [
                'name' => 'Luna Star',
                'password' => Hash::make('password'),
                'role' => 'artist',
                'phone' => '+1-555-0101',
                'bio' => 'Singer-songwriter from California',
                'is_active' => true,
            ]
        );

        Artist::firstOrCreate(
            ['user_id' => $artist1User->id],
            [
                'artist_name' => 'Luna Star',
                'genre' => 'Pop',
                'bio' => 'Luna Star is a rising pop artist known for her melodic vocals and heartfelt lyrics. Based in Los Angeles, California.',
                'country' => 'US',
                'payment_email' => 'luna.payments@example.com',
                'paypal_email' => 'luna@paypal.example',
                'total_earnings' => 4580.00,
                'available_balance' => 1250.00,
                'pending_balance' => 500.00,
                'spotify_profile_url' => 'https://open.spotify.com/artist/1x',
                'apple_music_profile_url' => 'https://music.apple.com/us/artist/luna-star',
                'youtube_profile_url' => 'https://youtube.com/@lunastar',
                'tidal_profile_url' => 'https://tidal.com/browse/artist/luna-star',
            ]
        );

        // ===== CREATE SAMPLE ARTIST 2: Phoenix Blaze =====
        $artist2User = User::firstOrCreate(
            ['email' => 'phoenix@example.com'],
            [
                'name' => 'Phoenix Blaze',
                'password' => Hash::make('password'),
                'role' => 'artist',
                'phone' => '+1-555-0102',
                'bio' => 'Hip-hop artist and producer from New York',
                'is_active' => true,
            ]
        );

        Artist::firstOrCreate(
            ['user_id' => $artist2User->id],
            [
                'artist_name' => 'Phoenix Blaze',
                'genre' => 'Hip-Hop',
                'bio' => 'Phoenix Blaze is a hard-hitting hip-hop artist from Brooklyn, New York. Known for sharp lyrics and heavy beats.',
                'country' => 'US',
                'payment_email' => 'phoenix.payments@example.com',
                'paypal_email' => 'phoenix@paypal.example',
                'total_earnings' => 8920.00,
                'available_balance' => 3200.00,
                'pending_balance' => 1500.00,
                'spotify_profile_url' => 'https://open.spotify.com/artist/2x',
                'apple_music_profile_url' => 'https://music.apple.com/us/artist/phoenix-blaze',
                'youtube_profile_url' => 'https://youtube.com/@phoenixblaze',
                'tidal_profile_url' => 'https://tidal.com/browse/artist/phoenix-blaze',
            ]
        );

        $this->command->info('Accounts seeded successfully!');
        $this->command->info('Admin 1: info@telemusic.io / TeleMusic2026');
        $this->command->info('Admin 2: admin@example.com / password');
        $this->command->info('Artist: mmstarlink532@gmail.com / 123456789 (MM Star Link)');
        $this->command->info('Artist 1: luna@example.com / password (Luna Star)');
        $this->command->info('Artist 2: phoenix@example.com / password (Phoenix Blaze)');
    }
}
