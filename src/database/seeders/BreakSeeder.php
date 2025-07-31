<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yasumi\Yasumi;

class BreakSeeder extends Seeder
{
    public function run(): void
    {
        $holidays = Yasumi::create('Japan', 2025);

        $startDate = Carbon::parse('2025-06-01');
        $endDate = Carbon::parse('2025-08-31');

        $minUserId = 1;
        $maxUserId = 21;

        while ($startDate->lte($endDate)) {
            $isWeekend = $startDate->isWeekend();
            $isHoliday = $holidays->isHoliday($startDate);

            if (!$isWeekend && !$isHoliday) {
                $attendances = DB::table('attendances')
                    ->where('date', $startDate->format('Y-m-d'))
                    ->whereBetween('user_id', [$minUserId, $maxUserId])
                    ->pluck('id');

                foreach ($attendances as $attendanceId) {
                    // 休憩①：12:00～13:00 のみ
                    DB::table('break_times')->insert([
                        'attendance_id' => $attendanceId,
                        'break_start' => $startDate->format('Y-m-d') . ' 12:00:00',
                        'break_end'   => $startDate->format('Y-m-d') . ' 13:00:00',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }

            $startDate->addDay();
        }
    }
}
