@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
<div class="page-title">
    <span class="title-bar"></span>
    <h2 class="title-text">承認済み勤怠詳細</h2>
</div>

<div class="attendance-detail-container">
    <table class="detail-table">
        <tr>
            <th>名前</th>
            <td class="center-cell">{{ $attendance->user->name }}</td>
        </tr>

        <tr>
            <th>日付</th>
            <td class="date-split">
                <div class="date-column"><span class="year">{{ $dateYear }}</span></div>
                <div class="date-column"><span class="day">{{ $dateDay }}</span></div>
            </td>
        </tr>

        <tr>
            <th>出勤・退勤</th>
            <td class="time-cell">
                <input type="time" name="requested_start_time" value="{{ old('requested_start_time', \Carbon\Carbon::parse($attendance->start_time)->format('H:i')) }}">
                ～
                <input type="time" name="requested_end_time" value="{{ old('requested_end_time', \Carbon\Carbon::parse($attendance->end_time)->format('H:i')) }}">
            </td>
        </tr>

        <tr>
            <th>休憩1</th>
            <td class="time-cell">
                @php
                $break1startFormatted = $customBreaks[0]['start'] ?? '';
                $break1endFormatted = $customBreaks[0]['end'] ?? '';
                @endphp

                <input type="time" name="requested_break1_start" value="{{ old('requested_break1_start', $break1startFormatted) }}">
                〜
                <input type="time" name="requested_break1_end" value="{{ old('requested_break1_end', $break1endFormatted) }}">
            </td>
        </tr>

        <tr>
            <th>休憩2</th>
            <td class="time-cell">
                @php
                $break2startFormatted = $customBreaks[1]['start'] ?? '';
                $break2endFormatted = $customBreaks[1]['end'] ?? '';
                @endphp

                <input type="time" name="requested_break2_start" value="{{ old('requested_break2_start', $break2startFormatted) }}">
                〜
                <input type="time" name="requested_break2_end" value="{{ old('requested_break2_end', $break2endFormatted) }}">
            </td>
        </tr>

        <tr>
            <th>備考</th>
            <td class="center-cell">
                <textarea name="requested_note" rows="2" style="width: 100%">{{ old('requested_note', $attendance->note ?? '') }}</textarea>
            </td>
        </tr>
    </table>
</div>
@endsection