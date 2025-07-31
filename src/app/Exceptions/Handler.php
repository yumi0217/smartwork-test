<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * 未認証時のリダイレクト処理
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        // guard名を取得（adminやweb）
        $guard = data_get($exception->guards(), 0);

        switch ($guard) {
            case 'admin':
                $loginRoute = 'admin.login';
                break;
            default:
                $loginRoute = 'login';
                break;
        }

        return redirect()->route($loginRoute);
    }
}
