<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ログイン画面（管理者）</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/layouts/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
</head>

<body>
    <header class="auth-header">
        <img src="{{ asset('images/コーチテックロゴ.png') }}" alt="COACHTECH ロゴ" class="logo">
    </header>

    <main>
        @yield('content')
        @yield('styles')
    </main>
</body>

</html>