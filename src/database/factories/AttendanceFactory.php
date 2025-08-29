<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // 必ず user_id を紐づけ
            'date' => now()->toDateString(),
            'start_time' => null,
            'end_time' => null,
            'note' => null,
        ];
    }
}
