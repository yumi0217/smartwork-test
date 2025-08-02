<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class AdminUserAttendanceController extends Controller
{
    public function index(User $user, Request $request)
    {
        $yearMonth = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($yearMonth . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $dates = [];
        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        $rawAttendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $attendances = [];

        foreach ($dates as $date) {
            $record = $rawAttendances->firstWhere('date', $date);

            if ($record) {
                $approved = CorrectionRequest::where('attendance_id', $record->id)
                    ->where('status', 'approved')
                    ->latest()
                    ->first();

                if ($approved) {
                    $record->start_time = $approved->requested_start_time;
                    $record->end_time = $approved->requested_end_time;
                    $record->note = $approved->requested_note;

                    $record->breaks_display = collect([
                        [
                            'start' => optional($approved->requested_break1_start)->format('H:i'),
                            'end'   => optional($approved->requested_break1_end)->format('H:i'),
                        ],
                        [
                            'start' => optional($approved->requested_break2_start)->format('H:i'),
                            'end'   => optional($approved->requested_break2_end)->format('H:i'),
                        ],
                    ]);
                } else {
                    $record->breaks_display = $record->breaks->take(2)->map(function ($b) {
                        return [
                            'start' => optional($b->break_start)->format('H:i'),
                            'end'   => optional($b->break_end)->format('H:i'),
                        ];
                    });
                }

                $attendances[$date] = $record;
            } else {
                $attendances[$date] = new Attendance([
                    'id' => 0,
                    'user_id' => $user->id,
                    'date' => $date,
                    'start_time' => null,
                    'end_time' => null,
                ]);
            }
        }

        return view('admin.users.attendances', compact('user', 'dates', 'attendances', 'yearMonth'));
    }

    public function show(Request $request, $id)
    {
        if ($id == 0 || $id === 'dummy') {
            $date = $request->input('date');
            $user = User::findOrFail($request->input('user_id'));

            $attendance = new Attendance([
                'id' => 0,
                'user_id' => $user->id,
                'date' => $date,
                'start_time' => null,
                'end_time' => null,
            ]);

            return view('admin.attendances.show', compact('attendance', 'user'));
        }

        $attendance = Attendance::findOrFail($id);
        $user = $attendance->user;

        return view('admin.attendances.show', compact('attendance', 'user'));
    }

    public function export($id, Request $request)
    {
        $user = User::findOrFail($id);
        $yearMonth = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($yearMonth . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $csvData = [];
        $csvData[] = ['日付', '出勤', '退勤', '休憩', '勤務時間', '備考'];

        foreach ($attendances as $attendance) {
            $approved = CorrectionRequest::where('attendance_id', $attendance->id)
                ->where('status', 'approved')
                ->latest()
                ->first();

            if ($approved) {
                $attendance->start_time = $approved->requested_start_time;
                $attendance->end_time = $approved->requested_end_time;
                $attendance->note = $approved->requested_note;
            }

            $breakSeconds = 0;
            foreach ($attendance->breaks as $break) {
                if ($break->break_start && $break->break_end) {
                    $breakSeconds += Carbon::parse($break->break_end)->diffInSeconds(Carbon::parse($break->break_start));
                }
            }

            $start = $attendance->start_time ? Carbon::parse($attendance->start_time) : null;
            $end = $attendance->end_time ? Carbon::parse($attendance->end_time) : null;
            $workSeconds = ($start && $end) ? $end->diffInSeconds($start) - $breakSeconds : 0;

            $format = function ($seconds) {
                $h = floor($seconds / 3600);
                $m = floor(($seconds % 3600) / 60);
                return sprintf('%d:%02d', $h, $m);
            };

            $csvData[] = [
                Carbon::parse($attendance->date)->format('Y/m/d'),
                $start?->format('H:i') ?? '',
                $end?->format('H:i') ?? '',
                $breakSeconds ? $format($breakSeconds) : '',
                $workSeconds ? $format($workSeconds) : '',
                $attendance->note ?? '',
            ];
        }

        $filename = $user->name . '_' . $yearMonth . '_勤怠.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $callback = function () use ($csvData) {
            $handle = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}
