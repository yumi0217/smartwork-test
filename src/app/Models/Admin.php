<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    // 認証時に使用するテーブル（admin_users テーブルなどを使う場合）
    protected $table = 'admins'; // ← テーブル名に合わせて変更

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * 隠したい属性（JSON化などで見せたくない）
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
