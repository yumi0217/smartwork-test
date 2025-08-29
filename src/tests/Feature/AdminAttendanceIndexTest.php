<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

/**
 * @property User $admin
 * @property User $user
 */
class AdminAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $date;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザーを作成
        /** @var \App\Models\User $this->admin */
        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // 一般ユーザーを作成
        /** @var \App\Models\User $this->user */
        $this->user = User::factory()->create([
            'is_admin' => false,
        ]);

        // 今日の日付
        $this->date = Carbon::today()->toDateString();

        // 勤怠データ作成（時刻はdatetime型として保存）
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $this->date,
            'start_time' => Carbon::parse($this->date . ' 09:00:00'),
            'end_time' => Carbon::parse($this->date . ' 18:00:00'),
        ]);

        // 休憩時間を登録（datetime型として保存）
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse($this->date . ' 12:00:00'),
            'break_end' => Carbon::parse($this->date . ' 13:00:00'),
        ]);
    }

    /** @test */
    public function 管理者が勤怠一覧画面にアクセスし当日勤怠情報を確認できる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/attendances');

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee(Carbon::today()->format('Y年n月j日'));
    }

    /** @test */
    public function 管理者が前日を押すと前日の勤怠情報が表示される()
    {
        $yesterday = Carbon::yesterday()->toDateString();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $yesterday,
            'start_time' => Carbon::parse($yesterday . ' 10:00:00'),
            'end_time' => Carbon::parse($yesterday . ' 19:00:00'),
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/attendances?date=' . $yesterday);

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee(Carbon::yesterday()->format('Y年n月j日'));
    }

    /** @test */
    public function 管理者が翌日を押すと翌日の勤怠情報が表示される()
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $tomorrow,
            'start_time' => Carbon::parse($tomorrow . ' 08:00:00'),
            'end_time' => Carbon::parse($tomorrow . ' 17:00:00'),
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/attendances?date=' . $tomorrow);

        $response->assertStatus(200);
        $response->assertSee('08:00');
        $response->assertSee('17:00');
        $response->assertSee(Carbon::tomorrow()->format('Y年n月j日'));
    }
}
