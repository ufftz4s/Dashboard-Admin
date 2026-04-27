<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tambahkan kodingan ini
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
        ]);

        // Jika ada seeder lain (seperti EmployeeSeeder), biarkan saja di bawahnya
        $this->call([
            EmployeeSeeder::class,
            AttendanceSeeder::class,
        ]);
    }
}