<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminUserAttendanceController;
use App\Http\Controllers\Admin\CorrectionRequestController;
use App\Http\Controllers\UserCorrectionRequestController;
use App\Models\Admin;
use Illuminate\Http\Request;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//一般社員会員登録
Route::middleware('guest:web')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('auth.register.store');
});

// 認証済みユーザーにメール確認通知を送信するルート
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware(['auth'])->name('verification.notice');

// メール内リンクのクリック後に呼ばれるルート
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // メール認証完了

    return redirect('/attendance'); // 出勤登録画面にリダイレクト
})->middleware(['auth', 'signed'])->name('verification.verify');

// 再送信処理
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '確認リンクを再送信しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 出勤登録画面（一般ユーザー）
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [UserAttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [UserAttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/clock-out', [UserAttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/attendance/break-start', [UserAttendanceController::class, 'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break-end', [UserAttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');
});

// 勤怠一覧画面（一般ユーザー）
Route::get('/attendance/list', [UserAttendanceController::class, 'index'])->name('attendance.index');


Route::middleware(['auth'])->group(function () {
    // 勤怠詳細＆修正申請フォーム画面（GET）
    Route::get('/attendance/detail/{id}', [UserAttendanceController::class, 'show'])->name('attendance.show');

    Route::get('/attendance/detail/date/{date}', [UserAttendanceController::class, 'showByDate'])->name('attendance.show.byDate');

    // 修正申請の送信（POST）→ フォームと一致させる
    Route::post('/correction-request/store', [UserCorrectionRequestController::class, 'store'])->name('correction_requests.store');

    // 修正申請の確認表示（承認待ち表示）
    Route::get('/correction-request/show/{id}', [UserCorrectionRequestController::class, 'show'])
        ->name('correction_requests.show');
});

//申請一覧画面
Route::middleware(['auth'])->group(function () {
    Route::get('/stamp_correction_request/list', [UserCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');

    Route::get('/stamp_correction_request/{id}', [UserCorrectionRequestController::class, 'show'])
        ->name('stamp_correction_request.show');
});



//一般ユーザーログイン
Route::get('/login', [UserAuthenticatedSessionController::class, 'create'])->name('auth.login');
Route::post('/login', [UserAuthenticatedSessionController::class, 'store'])->name('auth.login.store');
Route::post('/logout', [UserAuthenticatedSessionController::class, 'destroy'])->name('logout');

//管理者ログイン
Route::prefix('admin')->group(function () {
    // 未ログイン管理者だけアクセス可
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])->name('admin.login');
        Route::post('/login', [AdminAuthenticatedSessionController::class, 'store'])->name('admin.login.store');
    });
    // ログアウトはログイン中しか使わないため auth:admin を付けてもOK
    Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
});

// 勤怠一覧画面
//詳細画面
Route::prefix('admin')->middleware(['auth:admin'])->name('admin.')->group(function () {
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/{id}', [AttendanceController::class, 'show'])->name('attendances.show');
    Route::put('/attendances/{id}', [AttendanceController::class, 'update'])->name('attendances.update');

    // スタッフ一覧
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');

    // ⭐ スタッフ別勤怠一覧
    Route::get('/users/{user}/attendances', [AdminUserAttendanceController::class, 'index'])->name('users.attendances');
    Route::get('/users/{user}/attendances/export', [AdminUserAttendanceController::class, 'export'])->name('users.attendances.export');

    // 修正申請
    Route::get('/requests', [CorrectionRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{id}', [CorrectionRequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{id}/approve', [CorrectionRequestController::class, 'approve'])->name('requests.approve');
});
