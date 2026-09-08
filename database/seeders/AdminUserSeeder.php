<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@musicpublishing.com'],
            [
                'name' => 'System Admin',
                'phone' => '0000000000',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        Wallet::firstOrCreate(
            ['user_id' => $admin->id],
            ['balance' => 0, 'currency' => 'MMK']
        );
    }
}