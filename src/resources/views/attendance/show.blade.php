@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
<div class="page-title">
    <span class="title-bar"></span>
    <h2 class="title-text">勤怠詳細</h2>
</div>

<div class="attendance-detail-container">

    {{-- 常にフォームを表示 --}}
    <form method="POST" action="{{ route('correction_requests.store') }}">
        @csrf
        <input type="hidden" name="attendance_id" value="{{ $attendance_id }}">

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td class="center-cell">{{ Auth::user()->name }}</td>
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
                    @if($isEditable)
                    <input type="time" name="requested_start_time"
                        value="{{ old('requested_start_time', $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                    ～
                    <input type="time" name="requested_end_time"
                        value="{{ old('requested_end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                    @error('requested_end_time')
                    <p class="text-red">{{ $message }}</p>
                    @enderror
                    @else
                    @if($correctionRequest)
                    {{ \Carbon\Carbon::parse($correctionRequest->requested_start_time)->format('H:i') }}
                    ～ {{ \Carbon\Carbon::parse($correctionRequest->requested_end_time)->format('H:i') }}
                    @else
                    {{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '-' }}
                    ～ {{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '-' }}
                    @endif
                    @endif
                </td>
            </tr>

            <tr>
                <th>休憩</th>
                <td class="time-cell">
                    @php
                    $break1start = $customBreaks[0]['start'] ?? null;
                    $break1end = $customBreaks[0]['end'] ?? null;
                    @endphp


                    @if($isEditable)
                    <input type="time" name="requested_break_start"
                        value="{{ old('requested_break_start', $break1start) }}">
                    ～
                    <input type="time" name="requested_break_end"
                        value="{{ old('requested_break_end', $break1end) }}">
                    @error('requested_break_start')
                    <p class="text-red">{{ $message }}</p>
                    @enderror
                    @error('requested_break_end')
                    <p class="text-red">{{ $message }}</p>
                    @enderror
                    @else
                    @if (!is_null($break1start) && !is_null($break1end))

                    {{ \Carbon\Carbon::parse($break1start)->format('H:i') }}
                    ～ {{ \Carbon\Carbon::parse($break1end)->format('H:i') }}
                    @else
                    -
                    @endif
                    @endif
                </td>
            </tr>

            <tr>
                <th>休憩2</th>
                <td class="time-cell">
                    @php
                    $break2start = $customBreaks[1]['start'] ?? null;
                    $break2end = $customBreaks[1]['end'] ?? null;
                    @endphp

                    @if($isEditable)
                    <input type="time" name="requested_break2_start"
                        value="{{ old('requested_break2_start', $break2start) }}">
                    ～
                    <input type="time" name="requested_break2_end"
                        value="{{ old('requested_break2_end', $break2end) }}">
                    @error('requested_break2_start')
                    <p class="text-red">{{ $message }}</p>
                    @enderror
                    @error('requested_break2_end')
                    <p class="text-red">{{ $message }}</p>
                    @enderror
                    @else
                    @if (!is_null($break2start) && !is_null($break2end))

                    {{ \Carbon\Carbon::parse($break2start)->format('H:i') }}
                    ～ {{ \Carbon\Carbon::parse($break2end)->format('H:i') }}
                    @else
                    -
                    @endif
                    @endif
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td class="center-cell">
                    @if($isEditable)
                    <textarea name="requested_note" rows="2" style="width: 100%">{{ old('requested_note', $attendance->note ?? '') }}</textarea>
                    @error('requested_note')
                    <p class="text-red">{{ $message }}</p>
                    @enderror
                    @else
                    {{ $correctionRequest->requested_note ?? ($attendance->note ?? '-') }}
                    @endif
                </td>
            </tr>
        </table>

        @if($isEditable)
        <div class="button-area">
            <button type="submit" class="update-button">修正申請する</button>
        </div>
        @endif
    </form>

    {{-- 承認待ちの場合の表示 --}}
    @if(isset($isPending) && $isPending)
    <div class="button-area">
        <p class="error-message" style="color: red; text-align: center;">
            ※修正申請中（承認待ち）のため、内容の変更はできません。
        </p>
    </div>
    @endif

</div>
@endsection