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

    protected $casts = [
        'requested_start_time'   => 'datetime',
        'requested_end_time'     => 'datetime',
        'requested_break1_start' => 'datetime',
        'requested_break1_end'   => 'datetime',
        'requested_break2_start' => 'datetime',
        'requested_break2_end'   => 'datetime',
        // time 型カラムを Carbon インスタンスとして扱う
        'requested_start_time'   => 'datetime:H:i',
        'requested_end_time'     => 'datetime:H:i',
        'requested_break1_start' => 'datetime:H:i',
        'requested_break1_end'   => 'datetime:H:i',
        'requested_break2_start' => 'datetime:H:i',
        'requested_break2_end'   => 'datetime:H:i',
        'approved_at'            => 'datetime',
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
