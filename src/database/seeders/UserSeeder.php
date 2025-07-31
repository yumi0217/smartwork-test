<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        $names = [
            '山田 太郎',
            '西 怜奈',
            '増田 一世',
            '山本 敬吉',
            '秋田 朋美',
            '中西 教夫',
            '小林 翔太',
            '佐藤 春菜',
            '田中 佑樹',
            '高橋 美咲',
            '鈴木 大輔',
            '井上 結衣',
            '斉藤 拓真',
            '村上 梨花',
            '石井 和樹',
            '大野 沙織',
            '藤田 洋平',
            '遠藤 舞子',
            '青木 健太',
            '本田 千尋',
            '未打刻 太郎',

        ];

        $generalUsers = [];
        for ($i = 0; $i < count($names); $i++) {
            $generalUsers[] = [
                'name' => $names[$i],
                'email' => "user" . ($i + 1) . "@example.com",
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        DB::table('users')->insert($generalUsers);
    }
}
