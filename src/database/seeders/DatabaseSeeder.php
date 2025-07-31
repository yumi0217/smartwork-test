<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\AttendanceSeeder;
use Database\Seeders\BreakSeeder;
use Database\Seeders\CorrectionRequestSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
            AttendanceSeeder::class,
            BreakSeeder::class,
            CorrectionRequestSeeder::class,
        ]);
    }
}
