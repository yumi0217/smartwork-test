<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yasumi\Yasumi;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $startDate = Carbon::parse('2025-06-01');
        $endDate = Carbon::parse('2025-08-31');

        $holidays = Yasumi::create('Japan', 2025);

        for ($i = 1; $i <= 21; $i++) { // user_id = 1～21
            $date = $startDate->copy();

            while ($date->lte($endDate)) {
                // 土日 or 祝日をスキップ
                if ($date->isWeekend() || $holidays->isHoliday($date)) {
                    $date->addDay();
                    continue;
                }

                DB::table('attendances')->insert([
                    'user_id' => $i,
                    'date' => $date->toDateString(),
                    'start_time' => $date->toDateString() . ' 09:00:00',
                    'end_time' => $date->toDateString() . ' 18:00:00',
                    'note' => 'ダミー勤務（' . $date->toDateString() . '）',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $date->addDay();
            }
        }
    }
}
