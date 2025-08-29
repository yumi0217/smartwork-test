<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤ボタンが表示されて出勤処理ができる()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $postResponse = $this->post('/attendance');
        $postResponse->assertRedirect('/attendance');

        $followUp = $this->get('/attendance');
        $followUp->assertStatus(200);
        $followUp->assertSee('出勤中');
    }

    public function test_退勤済ユーザーには出勤ボタンが表示されない()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->setTime(9, 0),
            'end_time' => now()->setTime(18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        // より確実に「出勤ボタン」がないことを確認（HTML指定）
        $response->assertDontSee('<button type="submit">出勤</button>', false);
    }

    public function test_出勤時刻が勤怠一覧画面に表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->setTime(9, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('09:00');
    }
}
