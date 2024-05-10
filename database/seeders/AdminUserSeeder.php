<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "name" => "Admin",
            'email' => 'Admin207@gmail.com',
            'password' => bcrypt('AdminJesse'),
            'role' => 'admin',
            'phone_number' => '09876543435',
            'verification_code' => '0000',
            'cover_img' => 'default_cover.jpg',
        ]);
    }
}