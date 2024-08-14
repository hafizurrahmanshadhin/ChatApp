<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
        User::factory()->create([
            'name'     => 'user',
            'email'    => 'user@user.com',
            'password' => Hash::make('12345678'),
        ]);

        User::factory()->create([
            'name'     => 'admin',
            'email'    => 'admin@admin.com',
            'password' => Hash::make('12345678'),
        ]);

        User::factory()->create([
            'name'     => 'test',
            'email'    => 'test@test.com',
            'password' => Hash::make('12345678'),
        ]);
    }
}
