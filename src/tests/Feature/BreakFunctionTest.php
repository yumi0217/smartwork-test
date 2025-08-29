<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakFunctionTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // ユーザーを作成してログイン状態にする
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // 本日の出勤記録を作成
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->setTime(9, 0),
        ]);
    }

    public function test_休憩入ボタンが表示され休憩処理ができる()
    {
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $this->post('/attendance/break-start')
            ->assertRedirect('/attendance');

        $this->get('/attendance')
            ->assertSee('休憩中');
    }

    public function test_休憩は一日に何回でもできる()
    {
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');
        $this->post('/attendance/break-start');

        $this->get('/attendance')
            ->assertSee('休憩中');
    }

    public function test_休憩戻ボタンが表示され休憩解除できる()
    {
        $this->post('/attendance/break-start');

        $this->get('/attendance')
            ->assertSee('休憩戻');

        $this->post('/attendance/break-end');

        $this->get('/attendance')
            ->assertSee('出勤中');
    }

    public function test_休憩戻も一日に何回でもできる()
    {
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');

        $this->assertDatabaseCount('break_times', 2);
    }

    public function test_休憩時刻が勤怠一覧に表示される()
    {
        BreakTime::factory()->create([
            'attendance_id' => Attendance::first()->id,
            'break_start' => now()->setTime(12, 0),
            'break_end' => now()->setTime(12, 30),
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 「12:00」「12:30」の代わりに休憩合計時間として「0:30」が表示されることを確認
        $response->assertSee('0:30');
    }
}
