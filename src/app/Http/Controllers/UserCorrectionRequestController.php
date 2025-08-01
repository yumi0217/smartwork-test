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
        $correctionRequest = CorrectionRequest::with('attendance', 'attendance.breaks', 'attendance.user')->findOrFail($id);
        $attendance = $correctionRequest->attendance;

        $isPending = $correctionRequest->status === 'pending';

        $attendance->start_time = $correctionRequest->requested_start_time;
        $attendance->end_time   = $correctionRequest->requested_end_time;
        $attendance->note       = $correctionRequest->requested_note;

        // 空でも2枠確保
        $customBreaks = [
            [
                'start' => $correctionRequest->requested_break1_start ?? null,
                'end'   => $correctionRequest->requested_break1_end ?? null,
            ],
            [
                'start' => $correctionRequest->requested_break2_start ?? null,
                'end'   => $correctionRequest->requested_break2_end ?? null,
            ],
        ];

        $attendance->breaks_display = $customBreaks;

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
            'requested_break1_start' => $request->requested_break1_start ?: null,
            'requested_break1_end' => $request->requested_break1_end ?: null,
            'requested_break2_start' => $request->requested_break2_start ?: null,
            'requested_break2_end' => $request->requested_break2_end ?: null,
            'requested_note' => $request->requested_note,
            'status' => 'pending',
        ]);

        return redirect()->route('correction_requests.show', ['id' => $correctionRequest->id])
            ->with('success', '修正申請を送信しました');
    }

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

            $customBreaks = [
                [
                    'start' => $req->requested_break1_start ?? null,
                    'end'   => $req->requested_break1_end ?? null,
                ],
                [
                    'start' => $req->requested_break2_start ?? null,
                    'end'   => $req->requested_break2_end ?? null,
                ],
            ];

            $req->breaks_display = $customBreaks;
        }

        return view('stamp_correction_request.list', compact('requests'));
    }
}
