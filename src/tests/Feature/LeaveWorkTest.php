<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class LeaveWorkTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @var \App\Models\User
     */

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->setTime(9, 0),
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    /** @test */
    public function 退勤ボタンが表示され退勤処理ができる()
    {
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $response = $this->post('/attendance/clock-out');

        // DBに退勤時刻が保存されていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
        ]);

        $attendance = Attendance::where('user_id', $this->user->id)
            ->where('date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance->end_time);
    }


    /** @test */
    public function 勤怠一覧に退勤時刻が表示される()
    {
        $attendance = Attendance::first();
        $attendance->end_time = now()->setTime(18, 0);
        $attendance->save();

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('18:00');
    }
}
