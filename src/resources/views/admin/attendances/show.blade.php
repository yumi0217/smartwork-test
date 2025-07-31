@extends('layouts.admin')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/attendances/show.css') }}">
@endsection

@section('content')

<div class="page-title">
    <span class="title-bar"></span>
    <h2 class="title-text">勤怠詳細</h2>
</div>

<form method="POST" action="{{ route('admin.attendances.update', $attendance->id) }}">
    @csrf
    @method('PUT')

    <div class="attendance-detail-container">
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="date-split">
                    <span class="year">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                    <span class="day">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="start_time"
                        value="{{ old('start_time', $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                    ～
                    <input type="time" name="end_time"
                        value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                    <div class="error">
                        @if ($errors->has('start_time'))
                        <span class="error-message">{{ $errors->first('start_time') }}</span>
                        @elseif ($errors->has('end_time'))
                        <span class="error-message">{{ $errors->first('end_time') }}</span>
                        @endif
                    </div>
                </td>
            </tr>

            <tr>
                <th>休憩</th>
                <td>
                    <input type="time" name="break1_start"
                        value="{{ old('break1_start', isset($attendance->breaks[0]) && $attendance->breaks[0]->break_start ? \Carbon\Carbon::parse($attendance->breaks[0]->break_start)->format('H:i') : '') }}">
                    ～
                    <input type="time" name="break1_end"
                        value="{{ old('break1_end', isset($attendance->breaks[0]) && $attendance->breaks[0]->break_end ? \Carbon\Carbon::parse($attendance->breaks[0]->break_end)->format('H:i') : '') }}">
                    <div class="error">
                        @if ($errors->has('break1_start'))
                        <span class="error-message">{{ $errors->first('break1_start') }}</span>
                        @elseif ($errors->has('break1_end'))
                        <span class="error-message">{{ $errors->first('break1_end') }}</span>
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <th>休憩2</th>
                <td>
                    <input type="time" name="break2_start" value="{{ old('break2_start', isset($attendance->breaks[1]) && $attendance->breaks[1]->break_start ? \Carbon\Carbon::parse($attendance->breaks[1]->break_start)->format('H:i') : '') }}">
                    ～
                    <input type="time" name="break2_end" value="{{ old('break2_end', isset($attendance->breaks[1]) && $attendance->breaks[1]->break_end ? \Carbon\Carbon::parse($attendance->breaks[1]->break_end)->format('H:i') : '') }}">
                    <div class="error">
                        @if ($errors->has('break2_start'))
                        <span class="error-message">{{ $errors->first('break2_start') }}</span>
                        @elseif ($errors->has('break2_end'))
                        <span class="error-message">{{ $errors->first('break2_end') }}</span>
                        @endif
                    </div>
                </td>
            </tr>



            <tr>
                <th>備考</th>
                <td class="textarea-cell">
                    <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                    <div class="error">
                        @error('note') <span class="error-message">{{ $message }}</span> @enderror
                    </div>
                </td>
            </tr>

        </table>

        <div class="button-area">
            <button type="submit" class="update-button">修正</button>
        </div>
    </div>



</form>

@endsection