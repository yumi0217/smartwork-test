@extends('layouts.admin')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/users/index.css') }}">
@endsection

@section('content')
<div class="staff-container">
    <div class="page-title">
        <div class="title-bar"></div>
        <div class="title-text">スタッフ一覧</div>
    </div>

    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月別勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td><a href="{{ route('admin.users.attendances', $user->id) }}">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

</div>