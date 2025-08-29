<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Carbon\Carbon;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
        ]);
    }

    /** @test */
    public function 承認待ちの修正申請が一覧に表示される()
    {
        CorrectionRequest::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'status' => 'pending',
            'requested_note' => '体調不良',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/requests?status=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee($this->user->name);
        $response->assertSee('体調不良');
    }

    /** @test */
    public function 承認済みの修正申請が一覧に表示される()
    {
        CorrectionRequest::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'status' => 'approved',
            'requested_note' => '家庭の事情',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/requests?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee($this->user->name);
        $response->assertSee('家庭の事情');
    }

    /** @test */
    public function 修正申請の詳細画面で内容が正しく表示される()
    {
        $request = CorrectionRequest::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'status' => 'pending',
            'requested_start_time' => '09:30',
            'requested_end_time' => '18:30',
            'requested_note' => '交通遅延',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/requests/{$request->id}");

        $response->assertStatus(200);
        $response->assertSee('09:30');
        $response->assertSee('18:30');
        $response->assertSee('交通遅延');
    }

    /** @test */
    public function 承認ボタンを押すと修正申請が承認され勤怠に反映される()
    {
        $request = CorrectionRequest::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'status' => 'pending',
            'requested_start_time' => '10:00',
            'requested_end_time' => '19:00',
            'requested_note' => '病院通い',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post("/admin/requests/{$request->id}/approve");

        $response->assertRedirect('/admin/requests');

        $this->assertDatabaseHas('correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'start_time' => Carbon::today()->format('Y-m-d') . ' 10:00:00',
            'end_time' => Carbon::today()->format('Y-m-d') . ' 19:00:00',
            'note' => '病院通い',
        ]);
    }
}
