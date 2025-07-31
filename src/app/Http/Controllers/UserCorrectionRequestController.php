<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Http\Requests\CorrectionRequestRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class UserCorrectionRequestController extends Controller
{
    public function show($id)
    {
        // CorrectionRequest ID から取得
        $correctionRequest = CorrectionRequest::with('attendance', 'attendance.breaks', 'attendance.user')->findOrFail($id);

        $attendance = $correctionRequest->attendance;

        // 修正申請が「承認待ち」かどうか（必要なら）
        $isPending = $correctionRequest->status === 'pending';

        // 修正申請で上書き
        $attendance->start_time = $correctionRequest->requested_start_time;
        $attendance->end_time   = $correctionRequest->requested_end_time;
        $attendance->note       = $correctionRequest->requested_note;

        // 休憩情報を構成（空でないものだけ抽出）
        $customBreaks = collect([
            [
                'start' => $correctionRequest->requested_break_start,
                'end'   => $correctionRequest->requested_break_end,
            ],
            [
                'start' => $correctionRequest->requested_break2_start,
                'end'   => $correctionRequest->requested_break2_end,
            ],
        ])->filter(fn($b) => !empty($b['start']) && !empty($b['end']))->values()->toArray();

        // ★ この行を追加！
        $attendance->breaks_display = $customBreaks;

        // 日付表示用
        $date = Carbon::parse($attendance->date);
        $dateYear = $date->format('Y年');
        $dateDay = $date->format('n月j日');

        return view('correction_requests.show', [
            'attendance' => $attendance,
            'correctionRequest' => $correctionRequest,
            'dateYear' => $dateYear,
            'dateDay' => $dateDay,
            'user' => $attendance->user,
            'isPending' => $isPending,
            'customBreaks' => $customBreaks,
            'attendance_id' => $attendance->id,
            'isEditable' => true,
        ]);
    }



    public function store(CorrectionRequestRequest $request)
    {
        $correctionRequest = CorrectionRequest::create([
            'attendance_id' => $request->attendance_id,
            'user_id' => auth()->id(),
            'requested_start_time' => $request->requested_start_time,
            'requested_end_time' => $request->requested_end_time,
            'requested_break_start' => $request->requested_break_start ?: null,
            'requested_break_end' => $request->requested_break_end ?: null,
            'requested_break2_start' => $request->requested_break2_start ?: null,
            'requested_break2_end' => $request->requested_break2_end ?: null,
            'requested_note' => $request->requested_note,
            'status' => 'pending',
        ]);

        // 修正：CorrectionRequestのIDを渡す
        return redirect()->route('correction_requests.show', ['id' => $correctionRequest->id])
            ->with('success', '修正申請を送信しました');
    }



    // UserCorrectionRequestController.php

    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $user = Auth::user();

        $requests = CorrectionRequest::with('user')
            ->where('user_id', $user->id)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($requests as $req) {
            $req->target_date = \Carbon\Carbon::parse($req->requested_start_time)->format('Y-m-d');

            // 🔽 修正ここから追加（breaks_display を構成）
            $customBreaks = collect([
                [
                    'start' => $req->requested_break_start,
                    'end'   => $req->requested_break_end,
                ],
                [
                    'start' => $req->requested_break2_start,
                    'end'   => $req->requested_break2_end,
                ],
            ])->filter(fn($b) => !empty($b['start']) && !empty($b['end']))->values()->toArray();

            $req->breaks_display = $customBreaks;
            // 🔼 修正ここまで追加
        }

        return view('stamp_correction_request.list', compact('requests'));
    }
}
