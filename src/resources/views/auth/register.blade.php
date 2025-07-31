@extends('layouts.auth')

@section('title', '会員登録')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection


@section('content')
<div class="register-container">
    <h1 class="form-title">会員登録</h1>

    <form method="POST" action="{{ route('auth.register.store') }}">
        @csrf

        <!-- 名前 -->
        <div class="form-group">
            <label for="name">名前</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}">
            @error('name')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- メールアドレス -->
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}">
            @error('email')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- パスワード -->
        <div class="form-group">
            <label for="password">パスワード</label>
            <input id="password" type="password" name="password" autocomplete="new-password">
            @error('password')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- パスワード確認 -->
        <div class="form-group">
            <label for="password_confirmation">パスワード確認</label>
            <input id="password_confirmation" type="password" name="password_confirmation">
            @error('password_confirmation')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <!-- 登録ボタン -->
        <div class="form-group">
            <button type="submit" class="submit-button">登録する</button>
        </div>

        <!-- ログインリンク -->
        <div class="form-group">
            <a href="{{ route('auth.login') }}" class="login-link">ログインはこちら</a>
        </div>
    </form>
</div>
@endsection