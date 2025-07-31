<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', '管理画面')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/layouts/admin.css') }}">
    @yield('styles')
</head>

<body>
    <header class="admin-header">
        <div class="admin-header-inner">
            <div class="logo-area">
                <a href="{{ route('admin.attendances.index') }}">
                    <img src="{{ asset('images/コーチテックロゴ.png') }}" alt="COACHTECH" class="logo">
                </a>
            </div>
            <nav class="nav-menu">
                <a href="{{ route('admin.attendances.index') }}">勤怠一覧</a>
                <a href="{{ route('admin.users.index') }}">スタッフ一覧</a>
                <a href="{{ route('admin.requests.index') }}">申請一覧</a>
                <!-- ログアウトフォーム（POST） -->
                <form method="POST" action="{{ route('admin.logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit">
                        ログアウト
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>