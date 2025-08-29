<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    private $validData = [
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    /** @test */
    public function 名前が未入力の場合_バリデーションエラーになる()
    {
        $data = $this->validData;
        $data['name'] = '';

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /** @test */
    public function メールアドレスが未入力の場合_バリデーションエラーになる()
    {
        $data = $this->validData;
        $data['email'] = '';

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    public function パスワードが8文字未満の場合_バリデーションエラーになる()
    {
        $data = $this->validData;
        $data['password'] = 'short';
        $data['password_confirmation'] = 'short';

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /** @test */
    public function パスワードが一致しない場合_バリデーションエラーになる()
    {
        $data = $this->validData;
        $data['password_confirmation'] = 'mismatch123';

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    /** @test */
    public function パスワードが未入力の場合_バリデーションエラーになる()
    {
        $data = $this->validData;
        $data['password'] = '';
        $data['password_confirmation'] = '';

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    public function 正常な入力の場合_ユーザーが登録される()
    {
        $response = $this->post('/register', $this->validData);

        $response->assertRedirect('/email/verify');
        // 成功時のリダイレクト先に応じて修正
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }
}
