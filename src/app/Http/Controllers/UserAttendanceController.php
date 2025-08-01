<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\CorrectionRequest;

class UserAttendanceController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->with('breaks')
            ->first();

        $status = '勤務前';

        if ($attendance) {
            if ($attendance->end_time) {
                $status = '退勤済';
            } else {
                $lastBreak = $attendance->breaks->last();
                if ($lastBreak && is_null($lastBreak->break_end)) {
                    $status = '休憩中';
                } else {
                    $status = '出勤中';
                }
            }
        }

        return view('attendance.create', [
            'status' => $status,
            'date' => now()->isoFormat('YYYY年M月D日(ddd)'),
            'time' => now()->format('H:i'),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$existing) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'start_time' => now(),
            ]);
        }

        return redirect()->route('attendance.create')->with('status', '出勤を記録しました！');
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        if ($attendance && !$attendance->end_time) {
            $attendance->update([
                'end_time' => now(),
            ]);
        }

        return redirect()->route('attendance.create')->with('status', '退勤を記録しました！');
    }

    public function breakStart(Request $request)
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        if ($attendance) {
            $attendance->breaks()->create([
                'break_start' => now(),
            ]);
        }

        return redirect()->route('attendance.create')->with('status', '休憩開始を記録しました！');
    }

    public function breakEnd(Request $request)
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        if ($attendance) {
            $lastBreak = $attendance->breaks()
                ->whereNull('break_end')
                ->latest('break_start')
                ->first();

            if ($lastBreak) {
                $lastBreak->update([
                    'break_end' => now(),
                ]);
            }
        }

        return redirect()->route('attendance.create')->with('status', '休憩終了を記録しました！');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $currentMonth = $request->input('selected_month') ?? $request->input('month') ?? now()->format('Y-m');
        $parsedMonth = Carbon::parse($currentMonth);
        $startOfMonth = $parsedMonth->copy()->startOfMonth();
        $endOfMonth = $parsedMonth->copy()->endOfMonth();

        $dates = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        $attendanceData = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->with([
                'breaks',
                'correctionRequests' => fn($q) => $q->where('status', 'approved')->latest()
            ])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->date)->format('Y-m-d'));

        foreach ($attendanceData as $attendance) {
            $approved = $attendance->correctionRequests->first();

            if ($approved) {
                $attendance->start_time = $approved->requested_start_time;
                $attendance->end_time = $approved->requested_end_time;
                $attendance->note = $approved->requested_note;

                $breaks = collect([
                    ['start' => $approved->requested_break1_start, 'end' => $approved->requested_break1_end],
                    ['start' => $approved->requested_break2_start, 'end' => $approved->requested_break2_end],
                ]);
            } else {
                $breaks = $attendance->breaks->take(2);

                while ($breaks->count() < 2) {
                    $breaks->push((object)['break_start' => null, 'break_end' => null]);
                }

                $breaks = $breaks->map(fn($b) => [
                    'start' => $b->break_start,
                    'end' => $b->break_end,
                ]);
            }

            $attendance->customBreaks = $breaks->values()->toArray();
        }

        return view('attendance.index', compact('dates', 'attendanceData', 'currentMonth'));
    }

    public function show($id)
    {
        $attendance = Attendance::with('breaks', 'user')->find($id);

        if (!$attendance) {
            return redirect()->route('attendance.index')->with('error', '勤怠データが見つかりません');
        }

        $attendanceDate = Carbon::parse($attendance->date)->format('Y-m-d');

        $correctionRequest = CorrectionRequest::where('attendance_id', $id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('requested_start_time', $attendanceDate)
            ->latest()
            ->first();

        if ($correctionRequest) {
            $attendance->start_time = $correctionRequest->requested_start_time;
            $attendance->end_time = $correctionRequest->requested_end_time;
            $attendance->note = $correctionRequest->requested_note;

            $attendance->breaks_display = [
                ['start' => $correctionRequest->requested_break1_start, 'end' => $correctionRequest->requested_break1_end],
                ['start' => $correctionRequest->requested_break2_start, 'end' => $correctionRequest->requested_break2_end],
            ];

            return view('correction_requests.show', [
                'attendance' => $attendance,
                'attendance_id' => $attendance->id,
                'dateYear' => Carbon::parse($attendance->date)->format('Y年'),
                'dateDay' => Carbon::parse($attendance->date)->format('n月j日'),
                'correctionRequest' => $correctionRequest,
                'isEditable' => false,
                'user' => $attendance->user,
                'customBreaks' => $attendance->breaks_display,
            ]);
        }

        $breaks = $attendance->breaks->take(2);

        while ($breaks->count() < 2) {
            $breaks->push((object)['break_start' => null, 'break_end' => null]);
        }

        $attendance->breaks_display = $breaks->map(fn($b) => [
            'start' => optional($b->break_start)->format('H:i'),
            'end'   => optional($b->break_end)->format('H:i'),
        ])->values()->toArray();

        return view('attendance.show', [
            'attendance' => $attendance,
            'attendance_id' => $attendance->id,
            'dateYear' => Carbon::parse($attendance->date)->format('Y年'),
            'dateDay' => Carbon::parse($attendance->date)->format('n月j日'),
            'correctionRequest' => null,
            'isEditable' => true,
            'customBreaks' => $attendance->breaks_display,
        ]);
    }

    public function showByDate($date)
    {
        $user = Auth::user();
        $attendance = Attendance::with('breaks')->where('user_id', $user->id)->where('date', $date)->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $date,
                'start_time' => null,
                'end_time' => null,
            ]);
        }

        $breaks = $attendance->breaks->take(2);

        while ($breaks->count() < 2) {
            $breaks->push((object)['break_start' => null, 'break_end' => null]);
        }

        $attendance->breaks_display = $breaks->map(fn($b) => [
            'start' => $b->break_start,
            'end' => $b->break_end,
        ])->values()->toArray();

        $carbon = Carbon::parse($date);
        $dateYear = $carbon->format('Y年');
        $dateDay = $carbon->format('n月j日');

        return view('attendance.show', [
            'attendance' => $attendance,
            'attendance_id' => $attendance->id,
            'dateYear' => $dateYear,
            'dateDay' => $dateDay,
            'isEditable' => true,
            'correctionRequest' => null,
            'customBreaks' => $attendance->breaks_display,
        ]);
    }
}
