<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Skipping AdminUserSeeder in production. Create an admin with php artisan make:filament-user and assign Super Admin.');

            return;
        }

        User::query()->updateOrCreate(
            ['email' => 'admin@balajievents.test'],
            [
                'name' => 'Balaji Admin',
                'password' => Hash::make('Balaji@2026S'),
                'email_verified_at' => now(),
            ]
        );
    }
}
