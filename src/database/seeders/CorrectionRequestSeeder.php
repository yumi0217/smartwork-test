<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CorrectionRequestSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            DB::table('correction_requests')->insert([
                'attendance_id' => $i,
                'user_id' => 2,
                'requested_start_time' => '09:30:00',
                'requested_end_time' => '18:00:00',
                // ✅ 修正：break1
                'requested_break1_start' => '12:00:00',
                'requested_break1_end' => '13:00:00',
                'requested_break2_start' => $i % 2 === 0 ? '15:30:00' : null,
                'requested_break2_end'   => $i % 2 === 0 ? '15:45:00' : null,
                'requested_note' => '今日は遅れて出勤しました。',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
