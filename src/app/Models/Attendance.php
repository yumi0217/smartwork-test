<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BreakTime;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'note',
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
        return $this->breaks->take(2)->map(function ($break) {
            return [
                'start' => $break->break_start,
                'end' => $break->break_end,
            ];
        });
    }
}
