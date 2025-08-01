@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="page-title">
        <span class="title-bar"></span>
        <h1 class="title-text">勤怠一覧</h1>
    </div>

    <div class="calendar-controls">
        <form method="GET" action="{{ route('attendance.index') }}" class="month-form">
            <button type="submit" name="month" value="{{ \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m') }}" class="month-button left">
                ← 前月
            </button>

            <label for="month-picker" class="current-month-display">
                <img src="{{ asset('images/カレンダーアイコン.png') }}" alt="カレンダー" class="calendar-icon">
                <input
                    type="month"
                    id="month-picker"
                    name="selected_month"
                    value="{{ \Carbon\Carbon::parse($currentMonth)->format('Y-m') }}"
                    class="month-input"
                    onchange="this.form.submit()">
            </label>

            <button type="submit" name="month" value="{{ \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m') }}" class="month-button right">
                翌月 →
            </button>
        </form>
    </div>

    @php
    if (!function_exists('formatDuration')) {
    function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return sprintf('%d:%02d', $hours, $minutes);
    }
    }
    @endphp

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dates as $date)
            @php
            $attendance = $attendanceData[$date] ?? null;

            $start = $attendance?->start_time ? \Carbon\Carbon::parse($attendance->start_time) : null;
            $end = $attendance?->end_time ? \Carbon\Carbon::parse($attendance->end_time) : null;

            $breakSeconds = 0;
            $hasValidBreak = false;

            if ($attendance && isset($attendance->customBreaks)) {
            foreach ($attendance->customBreaks as $b) {
            if (!empty($b['start']) && !empty($b['end'])) {
            $breakSeconds += \Carbon\Carbon::parse($b['end'])->diffInSeconds(\Carbon\Carbon::parse($b['start']));
            $hasValidBreak = true;
            }
            }
            }

            $workSeconds = ($start && $end) ? $end->diffInSeconds($start) - $breakSeconds : null;

            $dateObj = \Carbon\Carbon::parse($date);
            $formattedDate = $dateObj->format('m/d');
            $weekdayMap = ['㈰', '㈪', '㈫', '㈬', '㈭', '㈮', '㈯'];
            @endphp

            <tr>
                <td>{{ $formattedDate }}{{ $weekdayMap[$dateObj->dayOfWeek] }}</td>
                <td>{{ $start ? $start->format('H:i') : '' }}</td>
                <td>{{ $end ? $end->format('H:i') : '' }}</td>
                <td>{{ $hasValidBreak ? formatDuration($breakSeconds) : '-' }}</td>
                <td>{{ $workSeconds ? formatDuration($workSeconds) : '' }}</td>
                <td>
                    @php
                    $correction = $attendance
                    ? \App\Models\CorrectionRequest::where('attendance_id', $attendance->id)
                    ->where('status', 'pending')
                    ->first()
                    : null;
                    @endphp

                    @if ($correction)
                    <a href="{{ route('correction_requests.show', ['id' => $correction->id]) }}">詳細</a>
                    @elseif ($attendance)
                    <a href="{{ route('attendance.show', $attendance->id) }}">詳細</a>
                    @else
                    <a href="{{ route('attendance.show.byDate', ['date' => $dateObj->format('Y-m-d')]) }}">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection