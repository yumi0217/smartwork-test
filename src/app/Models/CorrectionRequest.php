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
        'requested_break_start',     // 休憩1開始
        'requested_break_end',       // 休憩1終了
        'requested_break2_start',    // 休憩2開始 ←追加
        'requested_break2_end',      // 休憩2終了 ←追加
        'requested_note',
        'reason',
        'status',
        'approved_at',
    ];


    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
