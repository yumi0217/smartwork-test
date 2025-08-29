<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Carbon\Carbon;

class AttendanceTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_現在日時が画面に表示されている()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'remember_token' => null,
        ]);

        $this->actingAs($user);

        $now = Carbon::now();
        $expectedDate = $now->format('Y年n月j日'); // ※先頭ゼロなし
        $expectedTime = $now->format('H:i');

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}
