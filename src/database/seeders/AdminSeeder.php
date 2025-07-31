<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理者 花子',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
