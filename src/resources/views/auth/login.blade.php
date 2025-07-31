@extends('layouts.auth')

@section('content')
<div class="auth-container">
    <h1 class="auth-title">ログイン</h1>

    <form method="POST" action="{{ route('auth.login.store') }}">
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
            <button type="submit" class="auth-button form-control">ログインする</button>
        </div>
    </form>

    <div class="form-group" style="text-align:center; margin-top: 10px;">
        <a href="{{ route('register') }}">会員登録はこちら</a>
    </div>
</div>
@endsection