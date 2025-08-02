<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionRequest extends Model
{
    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_start_time',
        'requested_end_time',
        'requested_break1_start',     // ✅ 休憩1開始（修正済）
        'requested_break1_end',       // ✅ 休憩1終了（修正済）
        'requested_break2_start',     // 休憩2開始
        'requested_break2_end',       // 休憩2終了
        'requested_note',
        'status',
        'approved_at',
    ];

    // 勤怠とのリレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
