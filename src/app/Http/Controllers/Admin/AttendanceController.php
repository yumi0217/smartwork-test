<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Http\Requests\AdminCorrectionRequest;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ?? now()->toDateString();

        $users = User::where('is_admin', false)->get();

        foreach ($users as $user) {
            $attendance = Attendance::firstOrCreate(
                ['user_id' => $user->id, 'date' => $date],
                ['start_time' => null, 'end_time' => null]
            );
            $attendance->load('breaks');

            $latestApproved = $attendance->correctionRequests()
                ->where('status', 'approved')
                ->latest()
                ->first();

            if ($latestApproved) {
                $attendance->start_time = $latestApproved->requested_start_time;
                $attendance->end_time = $latestApproved->requested_end_time;
                $attendance->note = $latestApproved->requested_note;

                $attendance->breaks_display = collect([
                    [
                        'start' => $latestApproved->requested_break1_start,
                        'end'   => $latestApproved->requested_break1_end,
                    ],
                    [
                        'start' => $latestApproved->requested_break2_start,
                        'end'   => $latestApproved->requested_break2_end,
                    ],
                ]);
            } else {
                $attendance->breaks_display = $attendance->breaks->take(2)->map(function ($break) {
                    return [
                        'start' => $break->break_start,
                        'end'   => $break->break_end,
                    ];
                });
            }

            $user->attendanceForDate = $attendance;
        }

        return view('admin.attendances.index', [
            'attendances' => $users,
            'date' => $date,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->find($id);

        if (!$attendance) {
            $userId = request('user_id');
            $date = request('date');

            $attendance = Attendance::firstOrCreate(
                ['user_id' => $userId, 'date' => $date],
                ['start_time' => null, 'end_time' => null]
            );

            $attendance->load('user', 'breaks');
        }

        return view('admin.attendances.show', compact('attendance'));
    }

    public function update(AdminCorrectionRequest $request, $id)
    {
        $validated = $request->validated();
        $attendance = Attendance::with('breaks')->findOrFail($id);
        $date = $attendance->date;

        $attendance->start_time = $validated['start_time'] ? $date . ' ' . $validated['start_time'] : null;
        $attendance->end_time   = $validated['end_time'] ? $date . ' ' . $validated['end_time'] : null;
        $attendance->note       = $validated['note'] ?? null;
        $attendance->save();

        // 休憩1
        if (isset($attendance->breaks[0])) {
            $attendance->breaks[0]->break_start = $validated['break1_start'] ? $date . ' ' . $validated['break1_start'] : null;
            $attendance->breaks[0]->break_end   = $validated['break1_end'] ? $date . ' ' . $validated['break1_end'] : null;
            $attendance->breaks[0]->save();
        } elseif ($request->filled('break1_start') && $request->filled('break1_end')) {
            $attendance->breaks()->create([
                'break_start' => $date . ' ' . $validated['break1_start'],
                'break_end'   => $date . ' ' . $validated['break1_end'],
            ]);
        }

        // 休憩2
        if (isset($attendance->breaks[1])) {
            $attendance->breaks[1]->break_start = $validated['break2_start'] ? $date . ' ' . $validated['break2_start'] : null;
            $attendance->breaks[1]->break_end   = $validated['break2_end'] ? $date . ' ' . $validated['break2_end'] : null;
            $attendance->breaks[1]->save();
        } elseif ($request->filled('break2_start') && $request->filled('break2_end')) {
            $attendance->breaks()->create([
                'break_start' => $date . ' ' . $validated['break2_start'],
                'break_end'   => $date . ' ' . $validated['break2_end'],
            ]);
        }

        return redirect()->route('admin.attendances.index')->with('status', '勤怠情報を更新しました');
    }
}
