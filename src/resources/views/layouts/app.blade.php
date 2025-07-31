<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', '勤怠管理')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">
    @yield('styles')
</head>

<body>
    <header class="user-header">
        <div class="user-header-inner">
            <div class="logo-area">
                <a href="{{ route('attendance.create') }}">
                    <img src="{{ asset('images/コーチテックロゴ.png') }}" alt="COACHTECH" class="logo">
                </a>
            </div>
            <div class="nav-wrapper">
                <nav class="nav-menu">
                    <a href="{{ route('attendance.create') }}">勤怠</a>
                    <a href="{{ route('attendance.index') }}">勤怠一覧</a>
                    <a href="{{ route('stamp_correction_request.list') }}">申請</a>
                </nav>
                <form method="POST" action="{{ route('logout') }}" class="nav-form">
                    @csrf
                    <button type="submit" class="logout-btn">ログアウト</button>
                </form>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    @yield('scripts')
</body>

</html>