<?php

namespace Database\Factories;

use App\Models\CorrectionRequest;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CorrectionRequestFactory extends Factory
{
    protected $model = CorrectionRequest::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'requested_start_time' => $this->faker->time('H:i'),
            'requested_end_time' => $this->faker->time('H:i'),
            'requested_break1_start' => $this->faker->time('H:i'),
            'requested_break1_end' => $this->faker->time('H:i'),
            'requested_break2_start' => $this->faker->optional()->time('H:i'),
            'requested_break2_end' => $this->faker->optional()->time('H:i'),
            'requested_note' => $this->faker->realText(20),
            'status' => 'pending',
        ];
    }
}
