@extends('layouts.app')

@section('title', '出勤登録')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}">
@endsection

@section('content')
<div class="main-wrapper">
    <div class="container">
        <div class="status-badge">{{ $status }}</div>
        <div class="date">{{ $date }}</div>
        <div class="time">{{ $time }}</div>

        @if ($status === '勤務前')
        <form action="{{ route('attendance.store') }}" method="POST">
            @csrf
            <button type="submit">出勤</button>
        </form>
        @elseif ($status === '出勤中')
        <div class="button-group">
            <form action="{{ route('attendance.clockOut') }}" method="POST">
                @csrf
                <button type="submit" class="attendance-button black">退勤</button>
            </form>

            <form action="{{ route('attendance.breakStart') }}" method="POST">
                @csrf
                <button type="submit" class="attendance-button white">休憩入</button>
            </form>
        </div>
        @elseif ($status === '休憩中')
        <form action="{{ route('attendance.breakEnd') }}" method="POST">
            @csrf
            <button type="submit" class="attendance-button white">休憩戻</button>
        </form>
        @elseif ($status === '退勤済')
        <p>お疲れ様でした。</p>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        document.querySelector('.time').textContent = `${hours}:${minutes}`;
    }

    updateClock();
    setInterval(updateClock, 60000);
</script>
@endsection