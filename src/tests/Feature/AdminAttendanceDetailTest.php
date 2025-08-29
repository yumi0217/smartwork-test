<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

/**
 * @property User $admin
 * @property User $user
 * @property Attendance $attendance
 */
class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー作成
        /** @var \App\Models\User $this->admin */
        $this->admin = User::factory()->create(['is_admin' => true]);

        // 一般ユーザー作成
        /** @var \App\Models\User $this->user */
        $this->user = User::factory()->create(['is_admin' => false]);

        // 勤怠データ作成
        /** @var \App\Models\Attendance $this->attendance */
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::parse(Carbon::today()->toDateString() . ' 09:00:00'),
            'end_time' => Carbon::parse(Carbon::today()->toDateString() . ' 18:00:00'),
            'note' => '通常勤務',
        ]);
    }

    /** @test */
    public function 勤怠詳細画面に選択した情報が表示される()
    {
        $response = $this->actingAs($this->admin, 'admin')->get("/admin/attendances/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->user->name); // 一般ユーザー名が表示される
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('通常勤務');
    }

    /** @test */
    public function 出勤が退勤より後ならエラーメッセージ()
    {
        $response = $this->actingAs($this->admin, 'admin')->put("/admin/attendances/{$this->attendance->id}", [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'requested_break1_start' => '12:00',
            'requested_break1_end' => '13:00',
            'note' => '退勤より後',
        ]);

        $response->assertSessionHasErrors(['start_time']);
    }

    /** @test */
    public function 休憩開始が退勤より後ならエラーメッセージ()
    {
        $response = $this->from("/admin/attendances/{$this->attendance->id}")
            ->actingAs($this->admin, 'admin')
            ->put("/admin/attendances/{$this->attendance->id}", [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'requested_break1_start' => '19:00', // ← end_time より後
                'requested_break1_end' => '20:00',
                'note' => '休憩開始が退勤より後',
            ]);

        $response->assertRedirect("/admin/attendances/{$this->attendance->id}");
        $response->assertSessionHasErrors(['requested_break1_start']);
    }

    /** @test */
    public function 休憩終了が退勤より後ならエラーメッセージ()
    {
        $response = $this->from("/admin/attendances/{$this->attendance->id}")
            ->actingAs($this->admin, 'admin')
            ->put("/admin/attendances/{$this->attendance->id}", [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'requested_break1_start' => '12:00',
                'requested_break1_end' => '19:00', // ← end_time より後
                'note' => '休憩終了が退勤より後',
            ]);

        $response->assertRedirect("/admin/attendances/{$this->attendance->id}");
        $response->assertSessionHasErrors(['requested_break1_end']);
    }

    /** @test */
    public function 備考未入力でバリデーションエラー()
    {
        $response = $this->actingAs($this->admin, 'admin')->put("/admin/attendances/{$this->attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'requested_break1_start' => '12:00',
            'requested_break1_end' => '13:00',
            'note' => '',
        ]);

        $response->assertSessionHasErrors(['note']);
    }
}
