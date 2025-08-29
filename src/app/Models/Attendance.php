<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'note',
        'status',
        'edited_by_admin',
    ];

    protected $appends = ['breaks_display'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id')->orderBy('id');
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    /**
     * 合計休憩時間（hh:mm 形式）
     */
    public function getTotalBreakDurationAttribute()
    {
        $breakMinutes = $this->breaks->reduce(function ($carry, $break) {
            $start = $break->break_start ? Carbon::parse($break->break_start) : null;
            $end = $break->break_end ? Carbon::parse($break->break_end) : null;

            if ($start && $end && $end->gt($start)) {
                return $carry + $end->diffInMinutes($start);
            }

            return $carry;
        }, 0);

        return sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);
    }

    /**
     * 合計勤務時間（出勤〜退勤から休憩を引いた時間, hh:mm形式）
     */
    public function getTotalWorkDurationAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $workMinutes = $end->diffInMinutes($start);

        $breakMinutes = $this->breaks->reduce(function ($carry, $break) {
            $start = $break->break_start ? Carbon::parse($break->break_start) : null;
            $end = $break->break_end ? Carbon::parse($break->break_end) : null;

            if ($start && $end && $end->gt($start)) {
                return $carry + $end->diffInMinutes($start);
            }

            return $carry;
        }, 0);

        $netMinutes = max($workMinutes - $breakMinutes, 0);
        return sprintf('%d:%02d', floor($netMinutes / 60), $netMinutes % 60);
    }

    public function hasPendingCorrection()
    {
        return $this->correctionRequests()
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * アクセサ: breaks_display（最大2つの休憩データを返す）
     */
    public function getBreaksDisplayAttribute()
    {
        // 承認済み修正申請がある場合はそちらを優先
        $latestApproved = $this->correctionRequests()
            ->where('status', 'approved')
            ->latest()
            ->first();

        if ($latestApproved) {
            return collect([
                [
                    'start' => $latestApproved->requested_break1_start,
                    'end'   => $latestApproved->requested_break1_end,
                ],
                [
                    'start' => $latestApproved->requested_break2_start,
                    'end'   => $latestApproved->requested_break2_end,
                ],
            ]);
        }

        // それ以外は通常の休憩を最大2件まで表示
        return $this->breaks->take(2)->map(function ($break) {
            return [
                'start' => $break->break_start,
                'end'   => $break->break_end,
            ];
        });
    }
}
