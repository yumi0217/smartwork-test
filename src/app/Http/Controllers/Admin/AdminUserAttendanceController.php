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

        // 該当ユーザーのAttendance一覧取得（breaks含む）
        $rawAttendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $attendances = [];

        foreach ($dates as $date) {
            $record = $rawAttendances->firstWhere('date', $date);

            if ($record) {
                // 🔽 まず $approved を定義する
                $approved = CorrectionRequest::where('attendance_id', $record->id)
                    ->where('status', 'approved')
                    ->latest()
                    ->first();

                if ($approved) {
                    $record->start_time = $approved->requested_start_time;
                    $record->end_time = $approved->requested_end_time;
                    $record->note = $approved->requested_note;

                    $breaks = [];

                    if ($approved->requested_break_start && $approved->requested_break_end) {
                        $breaks[] = [
                            'start' => Carbon::parse($approved->requested_break_start),
                            'end' => Carbon::parse($approved->requested_break_end),
                        ];
                    }

                    if ($approved->requested_break2_start && $approved->requested_break2_end) {
                        $breaks[] = [
                            'start' => Carbon::parse($approved->requested_break2_start),
                            'end' => Carbon::parse($approved->requested_break2_end),
                        ];
                    }

                    $record->breaks_display = collect($breaks);
                } else {
                    $record->breaks_display = $record->breaks->map(function ($b) {
                        return [
                            'start' => $b->break_start,
                            'end' => $b->break_end,
                        ];
                    });
                }

                $attendances[$date] = $record;
            } else {
                // ダミーデータ（休憩なし）
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
            // ダミー表示用（詳細ページを空状態で表示）
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
            // 承認済みの修正申請があれば上書き
            $approved = CorrectionRequest::where('attendance_id', $attendance->id)
                ->where('status', 'approved')
                ->latest()
                ->first();

            if ($approved) {
                $attendance->start_time = $approved->requested_start_time;
                $attendance->end_time = $approved->requested_end_time;
                $attendance->note = $approved->requested_note;
            }

            // 休憩合計（秒）
            $breakSeconds = 0;
            foreach ($attendance->breaks as $break) {
                if ($break->break_start && $break->break_end) {
                    $breakSeconds += Carbon::parse($break->break_end)->diffInSeconds(Carbon::parse($break->break_start));
                }
            }

            // 勤務時間（秒）
            $start = $attendance->start_time ? Carbon::parse($attendance->start_time) : null;
            $end = $attendance->end_time ? Carbon::parse($attendance->end_time) : null;
            $workSeconds = ($start && $end) ? $end->diffInSeconds($start) - $breakSeconds : 0;

            // 書式変換
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
