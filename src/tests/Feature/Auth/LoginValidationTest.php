<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithoutRememberToken(array $attributes = [])
    {
        $user = User::factory()->make($attributes);
        unset($user->remember_token); // remember_token を削除
        $user->save();

        return $user;
    }

    public function test_メールアドレスが未入力の場合_バリデーションエラーになる()
    {
        $this->createUserWithoutRememberToken([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_パスワードが未入力の場合_バリデーションエラーになる()
    {
        $this->createUserWithoutRememberToken([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_登録内容と一致しない場合_バリデーションエラーになる()
    {
        $this->createUserWithoutRememberToken([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpass',
        ]);

        $response->assertSessionHasErrors();
    }
}
