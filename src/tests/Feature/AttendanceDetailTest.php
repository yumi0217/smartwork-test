<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;
    protected $break;

    protected function setUp(): void
    {
        parent::setUp();

        // ログインユーザー作成
        $this->user = User::factory()->create(['name' => 'テスト太郎']);

        // 勤怠情報作成（本日）
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::today()->setTime(9, 0),
            'end_time' => Carbon::today()->setTime(18, 0),
        ]);

        // 休憩1作成
        $this->break = BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => Carbon::today()->setTime(12, 0),
            'break_end' => Carbon::today()->setTime(13, 0),
        ]);
    }

    /** @test */
    public function 勤怠詳細画面にユーザーの名前が表示される()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->user->name); // 「テスト太郎」が表示される
    }

    /** @test */
    public function 勤怠詳細画面に正しい日付が表示される()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertSeeText(Carbon::parse($this->attendance->date)->format('Y年'));
        $response->assertSeeText(Carbon::parse($this->attendance->date)->format('n月j日'));
    }

    /** @test */
    public function 出勤退勤時間が正しく表示される()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 休憩時間が正しく表示される()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
