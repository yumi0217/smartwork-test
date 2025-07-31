<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;

class UserAuthenticatedSessionController extends Controller
{
    public function create()
    {
        if (Auth::guard('web')->check()) {
            return redirect('/attendance');
        }
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials)) {
            return redirect()->intended('/attendance'); // ユーザーのトップページなど
        }

        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
