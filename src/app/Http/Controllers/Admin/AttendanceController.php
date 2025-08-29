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

            $breakTimeMinutes = 0;

            $attendance->load('breaks');

            if ($attendance->edited_by_admin) {
                // ✅ 管理者が編集した場合は break テーブル優先！
                $attendance->breaks_display = $attendance->breaks->take(2)->map(function ($break) use (&$breakTimeMinutes) {
                    if ($break->break_start && $break->break_end) {
                        $start = \Carbon\Carbon::parse($break->break_start);
                        $end   = \Carbon\Carbon::parse($break->break_end);
                        $breakTimeMinutes += $start->diffInMinutes($end);
                    }
                    return [
                        'start' => $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '',
                        'end'   => $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '',
                    ];
                });
            } elseif ($latestApproved) {
                // ✅ 管理者編集でない場合だけ CorrectionRequest を使う
                $attendance->start_time = $latestApproved->requested_start_time;
                $attendance->end_time   = $latestApproved->requested_end_time;
                $attendance->note       = $latestApproved->requested_note;

                $attendance->breaks_display = collect([
                    [
                        'start' => $latestApproved->requested_break1_start ? \Carbon\Carbon::parse($latestApproved->requested_break1_start)->format('H:i') : '',
                        'end'   => $latestApproved->requested_break1_end   ? \Carbon\Carbon::parse($latestApproved->requested_break1_end)->format('H:i') : '',
                    ],
                    [
                        'start' => $latestApproved->requested_break2_start ? \Carbon\Carbon::parse($latestApproved->requested_break2_start)->format('H:i') : '',
                        'end'   => $latestApproved->requested_break2_end   ? \Carbon\Carbon::parse($latestApproved->requested_break2_end)->format('H:i') : '',
                    ]
                ]);

                if ($latestApproved->requested_break1_start && $latestApproved->requested_break1_end) {
                    $start = \Carbon\Carbon::parse($latestApproved->requested_break1_start);
                    $end = \Carbon\Carbon::parse($latestApproved->requested_break1_end);
                    $breakTimeMinutes += $start->diffInMinutes($end);
                }
                if ($latestApproved->requested_break2_start && $latestApproved->requested_break2_end) {
                    $start = \Carbon\Carbon::parse($latestApproved->requested_break2_start);
                    $end = \Carbon\Carbon::parse($latestApproved->requested_break2_end);
                    $breakTimeMinutes += $start->diffInMinutes($end);
                }
            } else {
                // 通常時の break 処理
                $attendance->breaks_display = $attendance->breaks->take(2)->map(function ($break) use (&$breakTimeMinutes) {
                    if ($break->break_start && $break->break_end) {
                        $start = \Carbon\Carbon::parse($break->break_start);
                        $end   = \Carbon\Carbon::parse($break->break_end);
                        $breakTimeMinutes += $start->diffInMinutes($end);
                    }
                    return [
                        'start' => $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '',
                        'end'   => $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '',
                    ];
                });
            }

            // hh:mm 形式に変換して保存
            $hours = floor($breakTimeMinutes / 60);
            $minutes = $breakTimeMinutes % 60;
            $attendance->break_time = sprintf('%d:%02d', $hours, $minutes);

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

        // 承認済みの修正申請があるか？
        $latestApproved = $attendance->correctionRequests()
            ->where('status', 'approved')
            ->latest()
            ->first();

        // 管理者が手動で編集したか？（start_time/end_time/note/breaks）
        $hasEdited = $attendance->edited_by_admin;

        // ✅ 管理者が編集しているなら editedビュー（最優先）
        if ($hasEdited) {
            $breaks = $attendance->breaks->values();

            $customBreaks = [
                [
                    'start' => optional($breaks->get(0))->break_start
                        ? \Carbon\Carbon::parse($breaks->get(0)->break_start)->format('H:i')
                        : '',
                    'end' => optional($breaks->get(0))->break_end
                        ? \Carbon\Carbon::parse($breaks->get(0)->break_end)->format('H:i')
                        : '',
                ],
                [
                    'start' => optional($breaks->get(1))->break_start
                        ? \Carbon\Carbon::parse($breaks->get(1)->break_start)->format('H:i')
                        : '',
                    'end' => optional($breaks->get(1))->break_end
                        ? \Carbon\Carbon::parse($breaks->get(1)->break_end)->format('H:i')
                        : '',
                ],
            ];

            return view('admin.attendances.edited', compact('attendance', 'customBreaks'));
        }


        // ✅ 管理者編集はないが承認済みがある場合は approvedビュー
        if ($latestApproved) {
            $attendance->start_time = $latestApproved->requested_start_time;
            $attendance->end_time = $latestApproved->requested_end_time;
            $attendance->note = $latestApproved->requested_note;

            $customBreaks = [
                [
                    'start' => $latestApproved->requested_break1_start ? \Carbon\Carbon::parse($latestApproved->requested_break1_start)->format('H:i') : '',
                    'end'   => $latestApproved->requested_break1_end   ? \Carbon\Carbon::parse($latestApproved->requested_break1_end)->format('H:i') : '',
                ],
                [
                    'start' => $latestApproved->requested_break2_start ? \Carbon\Carbon::parse($latestApproved->requested_break2_start)->format('H:i') : '',
                    'end'   => $latestApproved->requested_break2_end   ? \Carbon\Carbon::parse($latestApproved->requested_break2_end)->format('H:i') : '',
                ],
            ];

            return view('admin.attendances.approved', compact('attendance', 'customBreaks'));
        }

        // ✅ どちらもない通常表示
        return view('admin.attendances.show', compact('attendance'));
    }


    public function update(AdminCorrectionRequest $request, $id)
    {
        $validated = $request->validated();
        $attendance = Attendance::with('breaks')->findOrFail($id);
        $date = $attendance->date;

        // 勤怠データ更新
        $attendance->start_time = $validated['start_time'] ? $date . ' ' . $validated['start_time'] : null;
        $attendance->end_time   = $validated['end_time']   ? $date . ' ' . $validated['end_time']   : null;
        $attendance->note       = $validated['note'] ?? null;
        $attendance->edited_by_admin = true;
        $attendance->save();

        // 休憩1
        $break1 = $attendance->breaks->firstWhere('break_order', 1);
        if ($request->filled('break1_start') && $request->filled('break1_end')) {
            if ($break1) {
                $break1->update([
                    'break_start' => $date . ' ' . $validated['break1_start'],
                    'break_end'   => $date . ' ' . $validated['break1_end'],
                ]);
            } else {
                $attendance->breaks()->create([
                    'break_order' => 1,
                    'break_start' => $date . ' ' . $validated['break1_start'],
                    'break_end'   => $date . ' ' . $validated['break1_end'],
                ]);
            }
        } elseif ($break1) {
            $break1->delete();
        }

        // 休憩2
        $break2 = $attendance->breaks->firstWhere('break_order', 2);
        if ($request->filled('break2_start') && $request->filled('break2_end')) {
            if ($break2) {
                $break2->update([
                    'break_start' => $date . ' ' . $validated['break2_start'],
                    'break_end'   => $date . ' ' . $validated['break2_end'],
                ]);
            } else {
                $attendance->breaks()->create([
                    'break_order' => 2,
                    'break_start' => $date . ' ' . $validated['break2_start'],
                    'break_end'   => $date . ' ' . $validated['break2_end'],
                ]);
            }
        } elseif ($break2) {
            $break2->delete();
        }

        return redirect()->route('admin.attendances.index')->with('status', '勤怠情報を更新しました');
    }





    public function approvedShow($id)
    {
        $attendance = Attendance::with('user', 'breaks')->findOrFail($id);

        // 休憩を1つ目・2つ目に整理
        $customBreaks = [
            [
                'start' => optional($attendance->breaks[0])->break_start ? \Carbon\Carbon::parse($attendance->breaks[0]->break_start)->format('H:i') : '',
                'end'   => optional($attendance->breaks[0])->break_end   ? \Carbon\Carbon::parse($attendance->breaks[0]->break_end)->format('H:i') : '',
            ],
            [
                'start' => optional($attendance->breaks[1])->break_start ? \Carbon\Carbon::parse($attendance->breaks[1]->break_start)->format('H:i') : '',
                'end'   => optional($attendance->breaks[1])->break_end   ? \Carbon\Carbon::parse($attendance->breaks[1]->break_end)->format('H:i') : '',
            ]
        ];

        return view('admin.attendances.approved', compact('attendance', 'customBreaks'));
    }

    public function editedShow($id)
    {
        $attendance = Attendance::with(['user', 'breaks' => function ($query) {
            $query->orderBy('break_start'); // break_start順で整列
        }])->findOrFail($id);

        // approvedShow() と同じ構成に揃える
        $customBreaks = [
            [
                'start' => optional($attendance->breaks[0])->break_start
                    ? \Carbon\Carbon::parse($attendance->breaks[0]->break_start)->format('H:i')
                    : '',
                'end' => optional($attendance->breaks[0])->break_end
                    ? \Carbon\Carbon::parse($attendance->breaks[0]->break_end)->format('H:i')
                    : '',
            ],
            [
                'start' => optional($attendance->breaks[1])->break_start
                    ? \Carbon\Carbon::parse($attendance->breaks[1]->break_start)->format('H:i')
                    : '',
                'end' => optional($attendance->breaks[1])->break_end
                    ? \Carbon\Carbon::parse($attendance->breaks[1]->break_end)->format('H:i')
                    : '',
            ],
        ];

        return view('admin.attendances.edited', compact('attendance', 'customBreaks'));
    }
}
