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
            <td>
                {{ optional(\Carbon\Carbon::parse($request->requested_start_time ?? null))->format('H:i') }} ～
                {{ optional(\Carbon\Carbon::parse($request->requested_end_time ?? null))->format('H:i') }}
            </td>
        </tr>


        <tr>
            <th>休憩</th>
            <td>
                @if ($request->requested_break1_start && $request->requested_break1_end)
                {{ \Carbon\Carbon::parse($request->requested_break1_start)->format('H:i') }} ～
                {{ \Carbon\Carbon::parse($request->requested_break1_end)->format('H:i') }}
                @else
                -
                @endif
            </td>
        </tr>
        <tr>
            <th>休憩2</th>
            <td>
                @if ($request->requested_break2_start && $request->requested_break2_end)
                {{ \Carbon\Carbon::parse($request->requested_break2_start)->format('H:i') }} ～
                {{ \Carbon\Carbon::parse($request->requested_break2_end)->format('H:i') }}
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