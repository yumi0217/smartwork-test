<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BreakTime extends Model
{
    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
