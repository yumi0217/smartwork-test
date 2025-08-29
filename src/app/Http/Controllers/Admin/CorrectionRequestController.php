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

        // 空でも2枠確保して渡す
        $customBreaks = [
            [
                'start' => optional($request->requested_break1_start)->format('H:i'),
                'end'   => optional($request->requested_break1_end)->format('H:i'),
            ],
            [
                'start' => optional($request->requested_break2_start)->format('H:i'),
                'end'   => optional($request->requested_break2_end)->format('H:i'),
            ],
        ];

        return view('admin.requests.show', compact('request', 'customBreaks'));
    }

    /**
     * 承認処理
     */
    public function approve($id)
    {
        $request = CorrectionRequest::findOrFail($id);
        $attendance = $request->attendance;

        // 文字列をそのままCarbonにパース
        $attendance->start_time = $request->requested_start_time
            ? Carbon::parse($request->requested_start_time)
            : null;

        $attendance->end_time = $request->requested_end_time
            ? Carbon::parse($request->requested_end_time)
            : null;

        $attendance->note = $request->requested_note;
        $attendance->save();

        // ステータス更新
        $request->status = 'approved';
        $request->approved_at = now();
        $request->save();

        // 休憩を削除して再登録
        $attendance->breaks()->delete();

        if ($request->requested_break1_start && $request->requested_break1_end) {
            $attendance->breaks()->create([
                'break_start' => Carbon::parse($request->requested_break1_start),
                'break_end'   => Carbon::parse($request->requested_break1_end),
            ]);
        }

        if ($request->requested_break2_start && $request->requested_break2_end) {
            $attendance->breaks()->create([
                'break_start' => Carbon::parse($request->requested_break2_start),
                'break_end'   => Carbon::parse($request->requested_break2_end),
            ]);
        }

        return redirect()->route('admin.requests.index')->with('success', '申請を承認しました。');
    }
}
