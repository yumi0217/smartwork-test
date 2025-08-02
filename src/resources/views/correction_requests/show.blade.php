@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/correction_requests/show.css') }}">
@endsection

@section('content')
@php
$breaks = isset($customBreaks)
? (is_array($customBreaks) ? $customBreaks : collect($customBreaks)->toArray())
: (is_array($attendance->breaks_display) ? $attendance->breaks_display : collect($attendance->breaks_display)->toArray());
@endphp

<div class="page-title">
    <span class="title-bar"></span>
    <h2 class="title-text">勤怠詳細（承認待ち）</h2>
</div>

<div class="attendance-detail-container">
    <table class="detail-table">
        <tr>
            <th>名前</th>
            <td class="center-cell">{{ $user->name }}</td>
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
                {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}
                ～
                {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}
            </td>
        </tr>

        <tr>
            <th>休憩1</th>
            <td class="time-cell">
                @if(isset($breaks[0]['start']) || isset($breaks[0]['end']))
                {{ isset($breaks[0]['start']) && $breaks[0]['start'] ? \Carbon\Carbon::parse($breaks[0]['start'])->format('H:i') : '-' }}
                ～
                {{ isset($breaks[0]['end']) && $breaks[0]['end'] ? \Carbon\Carbon::parse($breaks[0]['end'])->format('H:i') : '-' }}
                @else
                -
                @endif
            </td>
        </tr>

        <tr>
            <th>休憩2</th>
            <td class="time-cell">
                @if(isset($breaks[1]['start']) || isset($breaks[1]['end']))
                {{ isset($breaks[1]['start']) && $breaks[1]['start'] ? \Carbon\Carbon::parse($breaks[1]['start'])->format('H:i') : '-' }}
                ～
                {{ isset($breaks[1]['end']) && $breaks[1]['end'] ? \Carbon\Carbon::parse($breaks[1]['end'])->format('H:i') : '-' }}
                @else
                -
                @endif
            </td>
        </tr>

        <tr>
            <th>備考</th>
            <td class="center-cell">
                {{ $correctionRequest->requested_note ?? ($attendance->note ?? '-') }}
            </td>
        </tr>
    </table>

    @if(isset($correctionRequest) && $correctionRequest->status === 'pending')
    <div class="button-area">
        <p class="error-message">※修正申請中（承認待ち）のため、内容の変更はできません。</p>
    </div>
    @endif
</div>
@endsection