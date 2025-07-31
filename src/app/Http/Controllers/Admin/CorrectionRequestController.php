<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CorrectionRequest;
use Carbon\Carbon;


class CorrectionRequestController extends Controller
{
    /**
     * 一覧表示（承認待ち／承認済み）
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $requests = CorrectionRequest::with('user', 'attendance')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.requests.index', compact('requests'));
    }

    /**
     * 詳細表示（承認 or 承認済み） - show.blade.php に統一
     */
    public function show($id)
    {
        $request = CorrectionRequest::with(['user', 'attendance.breaks'])->findOrFail($id);

        return view('admin.requests.show', compact('request'));
    }

    /**
     * 承認処理
     */
    public function approve($id)
    {
        $request = CorrectionRequest::findOrFail($id);
        $attendance = $request->attendance;

        // 日付を元にフルの datetime に変換する（必要）
        $date = $attendance->date; // 出勤日
        $start = $request->requested_start_time ? Carbon::createFromFormat('Y-m-d H:i:s', "$date {$request->requested_start_time}") : null;
        $end = $request->requested_end_time ? Carbon::createFromFormat('Y-m-d H:i:s', "$date {$request->requested_end_time}") : null;

        // 勤怠データを更新
        $attendance->start_time = $start;
        $attendance->end_time = $end;
        $attendance->note = $request->requested_note;
        $attendance->save();

        // ステータス更新
        $request->status = 'approved';
        $request->approved_at = now();
        $request->save();

        return redirect()->route('admin.requests.index')->with('success', '申請を承認しました。');
    }
}
