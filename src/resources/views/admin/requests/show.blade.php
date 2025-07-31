@extends('layouts.admin')

@section('title', '修正申請承認画面（管理者）')
@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/requests/show.css') }}">
@endsection

@section('content')
<div class="detail-container">
    <h1 class="page-title">勤怠詳細</h1>

    <table class="detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $request->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y年n月j日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>{{ $request->requested_start_time }} ～ {{ $request->requested_end_time }}</td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                @if ($request->requested_break_start && $request->requested_break_end)
                {{ $request->requested_break_start }} ～ {{ $request->requested_break_end }}
                @else
                -
                @endif
            </td>
        </tr>
        <tr>
            <th>休憩2</th>
            <td>
                @if ($request->requested_break_start_2 && $request->requested_break_end_2)
                {{ $request->requested_break_start_2 }} ～ {{ $request->requested_break_end_2 }}
                @else
                -
                @endif
            </td>
        </tr>

        <tr>
            <th>備考</th>
            <td>{{ $request->requested_note }}</td>
        </tr>
    </table>

    <!-- ボタン・ラベル配置エリア -->
    <div class="button-container">
        @if ($request->status === 'pending')
        <form method="POST" action="{{ route('admin.requests.approve', $request->id) }}">
            @csrf
            <button type="submit" class="approve-btn">承認</button>
        </form>
        @else
        <div class="approved-label">承認済み</div>
        @endif
    </div>


</div>
@endsection