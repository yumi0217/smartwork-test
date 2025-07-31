@extends('layouts.auth')

@section('title', 'メール認証')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-container">
    <p class="main-message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    <!-- 認証ボタン（POST） -->
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="verify-button">認証はこちらから</button>
    </form>

    <!-- 再送用フォーム -->
    <form method="POST" action="{{ route('verification.send') }}" style="margin-top: 16px;">
        @csrf
        <button type="submit" class="resend-link" style="background: none; border: none; color: #007bff; text-decoration: underline; cursor: pointer;">
            認証メールを再送する
        </button>
    </form>
</div>
@endsection