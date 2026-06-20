<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'learner@app-english.test'],
            [
                'name' => 'Learner Demo',
                'password' => 'password',
                'role' => UserRole::Learner,
                'email_verified_at' => now(),
                'tokens' => config('tokens.initial_balance', 100),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@app-english.test'],
            [
                'name' => 'Admin Demo',
                'password' => 'password',
                'role' => UserRole::Administrator,
                'email_verified_at' => now(),
                'tokens' => 0,
            ],
        );
    }
}
