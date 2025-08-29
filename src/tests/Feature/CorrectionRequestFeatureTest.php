<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class CorrectionRequestFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User */
    protected $user;

    /** @var \App\Models\Attendance */
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // @phpstan-ignore-next-line: Factoryで生成されるため問題なし
        $this->user = User::factory()->create();

        $date = Carbon::today()->toDateString();

        // @phpstan-ignore-next-line: Factoryで生成されるため問題なし
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => $date,
            'start_time' => Carbon::parse("{$date} 09:00:00"),
            'end_time' => Carbon::parse("{$date} 18:00:00"),
            'note' => '定時勤務',
        ]);
    }

    /** @test */
    public function 出勤が退勤より後ならエラー()
    {
        $this->actingAs($this->user);

        $response = $this->post('/correction-request/store', [
            'attendance_id' => $this->attendance->id,
            'requested_start_time' => '19:00',
            'requested_end_time' => '18:00',
            'requested_break1_start' => '12:00',
            'requested_break1_end' => '13:00',
            'requested_note' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['requested_end_time']);
    }

    /** @test */
    public function 休憩開始が退勤より後ならエラー()
    {
        $this->actingAs($this->user);

        $response = $this->post('/correction-request/store', [
            'attendance_id' => $this->attendance->id,
            'requested_start_time' => '09:00',
            'requested_end_time' => '18:00',
            'requested_break1_start' => '19:00',
            'requested_break1_end' => '19:30',
            'requested_note' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['requested_break1_start']);
    }

    /** @test */
    public function 休憩終了が退勤より後ならエラー()
    {
        $this->actingAs($this->user);

        $response = $this->post('/correction-request/store', [
            'attendance_id' => $this->attendance->id,
            'requested_start_time' => '09:00',
            'requested_end_time' => '18:00',
            'requested_break1_start' => '17:00',
            'requested_break1_end' => '19:30',
            'requested_note' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['requested_break1_end']);
    }

    /** @test */
    public function 備考欄が空ならエラー()
    {
        $this->actingAs($this->user);

        $response = $this->post('/correction-request/store', [
            'attendance_id' => $this->attendance->id,
            'requested_start_time' => '09:00',
            'requested_end_time' => '18:00',
            'requested_break1_start' => '12:00',
            'requested_break1_end' => '13:00',
            'requested_note' => '',
        ]);

        $response->assertSessionHasErrors(['requested_note']);
    }
}
