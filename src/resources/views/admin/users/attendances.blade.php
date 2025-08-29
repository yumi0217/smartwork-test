@extends('layouts.admin') {{-- 管理者用レイアウト --}}

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/users/attendances.css') }}">
@endsection

@section('content')

{{-- ▼ タイトル部分 --}}
<div class="page-title-container">
    <div class="page-title">
        <div class="title-bar"></div>
        <div class="title-text">{{ $user->name }}さんの勤怠</div>
    </div>
</div>

{{-- ▼ 月選択 UI --}}
<div class="month-selector">
    <div class="month-nav-left">
        <a href="{{ route('admin.users.attendances', ['user' => $user->id, 'month' => \Carbon\Carbon::parse($yearMonth)->subMonth()->format('Y-m')]) }}">← 前月</a>
    </div>

    <form method="GET" action="{{ route('admin.users.attendances', ['user' => $user->id]) }}" class="month-form">
        <label for="month">
            <img src="{{ asset('images/カレンダーアイコン.png') }}" class="calendar-icon">
        </label>
        <input type="month" name="month" id="month" value="{{ $yearMonth }}" onchange="this.form.submit()">
    </form>

    <div class="month-nav-right">
        <a href="{{ route('admin.users.attendances', ['user' => $user->id, 'month' => \Carbon\Carbon::parse($yearMonth)->addMonth()->format('Y-m')]) }}">翌月 →</a>
    </div>
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

{{-- ▼ 勤怠テーブル --}}
<div class="attendance-container">
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
            $attendance = $attendances[$date] ?? null;
            $carbon = \Carbon\Carbon::parse($date);

            $start = $attendance && $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time) : null;
            $end = $attendance && $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time) : null;

            $breakSeconds = 0;

            if ($attendance && isset($attendance->customBreaks)) {
            foreach ($attendance->customBreaks as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
            $breakDate = \Carbon\Carbon::parse($attendance->date);
            $breakStart = $breakDate->copy()->setTimeFromTimeString($break['start']);
            $breakEnd = $breakDate->copy()->setTimeFromTimeString($break['end']);
            $breakSeconds += $breakEnd->diffInSeconds($breakStart);
            }
            }
            }

            $workSeconds = ($start && $end) ? $end->diffInSeconds($start) - $breakSeconds : null;
            @endphp
            <tr>
                <td>{{ $carbon->format('m/d') }}（{{ ['日','月','火','水','木','金','土'][$carbon->dayOfWeek] }}）</td>
                <td>{{ $start ? $start->format('H:i') : '' }}</td>
                <td>{{ $end ? $end->format('H:i') : '' }}</td>
                <td>{{ $breakSeconds > 0 ? formatDuration($breakSeconds) : '' }}</td>
                <td>{{ $workSeconds > 0 ? formatDuration($workSeconds) : '' }}</td>
                <td>
                    <a href="{{ route('admin.attendances.show', ['id' => $attendance->id ?? 0, 'user_id' => $user->id, 'date' => $date]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<form method="GET" action="{{ route('admin.users.attendances.export', ['user' => $user->id]) }}">
    <input type="hidden" name="month" value="{{ $yearMonth }}">
    <div class="csv-button-fixed">
        <button type="submit" class="export-button">CSV出力</button>
    </div>
</form>

@endsection