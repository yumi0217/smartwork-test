<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;

class AdminAuthenticatedSessionController extends Controller
{
    public function create()
    {
        if (Auth::guard('admin')->check()) {
            return redirect('/admin/attendances');
        }
        return view('admin.login');
    }

    public function store(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->intended('/admin/attendances'); // 管理者用TOP
        }

        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
