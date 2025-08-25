<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Tạo Super Admin
        User::updateOrCreate(
            ['email' => 'admin@golfreview.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@golfreview.com',
                'password' => Hash::make('Admin@123456'),
                'role' => 'admin',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Tạo Editor
        User::updateOrCreate(
            ['email' => 'editor@golfreview.com'],
            [
                'name' => 'Editor',
                'email' => 'editor@golfreview.com',
                'password' => Hash::make('Editor@123456'),
                'role' => 'editor',
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        // Tạo Reviewer
        User::updateOrCreate(
            ['email' => 'reviewer@golfreview.com'],
            [
                'name' => 'Reviewer',
                'email' => 'reviewer@golfreview.com',
                'password' => Hash::make('Reviewer@123456'),
                'role' => 'reviewer',
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );
    }
}