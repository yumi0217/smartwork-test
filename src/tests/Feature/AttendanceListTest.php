<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        Carbon::setTestNow(Carbon::parse('2025-08-01'));

        // 今月分: 7/30, 7/31, 8/01 の3日間
        $dates = [
            Carbon::parse('2025-07-30'),
            Carbon::parse('2025-07-31'),
            Carbon::parse('2025-08-01'),
        ];

        foreach ($dates as $date) {
            Attendance::factory()->create([
                'user_id' => $this->user->id,
                'date' => $date->toDateString(),
            ]);
        }

        // 先月（7月）の別の1日
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::parse('2025-07-15')->toDateString(),
        ]);
    }


    /** @test */
    /** @test */
    public function 勤怠一覧に自分の勤怠情報が全て表示される()
    {
        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);

        $currentMonth = Carbon::now()->format('Y-m');

        $attendances = Attendance::where('user_id', $this->user->id)
            ->where('date', 'like', "{$currentMonth}%")
            ->get();

        foreach ($attendances as $attendance) {
            $formattedDate = Carbon::parse($attendance->date)->format('m/d');
            $this->assertStringContainsString($formattedDate, $response->getContent());
        }
    }


    /** @test */
    public function 勤怠一覧で現在の月が初期表示される()
    {
        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y-m'));
    }

    /** @test */
    public function 勤怠一覧で前月ボタンを押すと前月の勤怠が表示される()
    {
        $previousMonth = Carbon::now()->subMonth()->format('Y-m');
        $response = $this->actingAs($this->user)->get('/attendance/list?month=' . $previousMonth);
        $response->assertStatus(200);
        $response->assertSee($previousMonth);
    }

    /** @test */
    public function 勤怠一覧で翌月ボタンを押すと翌月の勤怠が表示される()
    {
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');
        $response = $this->actingAs($this->user)->get('/attendance/list?month=' . $nextMonth);
        $response->assertStatus(200);
        $response->assertSee($nextMonth);
    }

    /** @test */
    public function 詳細リンクを押すと詳細画面に遷移できる()
    {
        $attendance = Attendance::where('user_id', $this->user->id)->first();

        $response = $this->actingAs($this->user)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細'); // タイトルなどで検出
    }
}
