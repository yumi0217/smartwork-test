@extends('layouts.admin')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/attendances/index.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="page-title">
        <span class="title-bar"></span>
        <h1 class="title-text">{{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠</h1>
    </div>

    <div class="date-selector">
        <!-- 前日 -->
        <form method="GET" action="{{ route('admin.attendances.index') }}">
            <input type="hidden" name="date" value="{{ \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d') }}">
            <button type="submit" class="date-nav">← 前日</button>
        </form>

        <!-- 日付カレンダー -->
        <form method="GET" action="{{ route('admin.attendances.index') }}" class="date-form">
            <img src="{{ asset('images/カレンダーアイコン.png') }}" alt="カレンダーアイコン" style="width: 20px;">
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()">
        </form>

        <!-- 翌日 -->
        <form method="GET" action="{{ route('admin.attendances.index') }}">
            <input type="hidden" name="date" value="{{ \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d') }}">
            <button type="submit" class="date-nav">翌日 →</button>
        </form>
    </div>

    {{-- ★★★ ここに追加 ★★★ --}}
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
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $user)
            @php
            $attendance = $user->attendanceForDate;


            $start = isset($attendance->start_time) ? \Carbon\Carbon::parse($attendance->start_time) : null;
            $end = isset($attendance->end_time) ? \Carbon\Carbon::parse($attendance->end_time) : null;

            $breakSeconds = 0;
            if ($attendance && $attendance->breaks_display) {
            foreach ($attendance->breaks_display as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
            $breakStart = \Carbon\Carbon::parse($break['start']);
            $breakEnd = \Carbon\Carbon::parse($break['end']);
            $breakSeconds += $breakEnd->diffInSeconds($breakStart);
            }
            }
            }

            $workSeconds = ($start && $end) ? $end->diffInSeconds($start) - $breakSeconds : null;
            @endphp

            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $start ? $start->format('H:i') : '' }}</td>
                <td>{{ $end ? $end->format('H:i') : '' }}</td>
                <td>{{ $breakSeconds > 0 ? formatDuration($breakSeconds) : '' }}</td>
                <td>{{ $workSeconds > 0 ? formatDuration($workSeconds) : '' }}</td>
                <td>
                    @if (!empty($attendance))
                    <a href="{{ route('admin.attendances.show', $attendance->id) }}">詳細</a>
                    @else
                    -
                    @endif
                </td>
            </tr>
            @endforeach


            @if ($attendances->isEmpty())
            <tr>
                <td colspan="6">データが見つかりません</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection