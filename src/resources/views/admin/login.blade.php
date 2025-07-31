@extends('layouts.auth')

@section('content')
<div class="auth-container">
    <h1 class="auth-title">管理者ログイン</h1>

    <form method="POST" action="{{ route('admin.login.store') }}">
        @csrf

        {{-- メールアドレス --}}
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}">
            @error('email')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="form-group">
            <label for="password">パスワード</label>
            <input id="password" type="password" name="password">
            @error('password')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="auth-button form-control">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection