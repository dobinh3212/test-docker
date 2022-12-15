<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }

    //test create tài khoản
    public function testCreateUser()
    {
        $user = User::factory()->create([
            'name' => 'binhdv',
            'email' => 'binhdv@example.com',
            'password' => bcrypt($password = '12345678'),
        ]);
        $this->assertEquals(1, count(array($user)));
    }

    //test update tài khoản
    public function testUpdateUser()
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('1234567890'),
        ]);
        $result = $user->update([
            'email' => 'admin11@test.com',
        ]);
        $this->assertEquals(true, $result);
    }

    //test delete tài khoản
    public function testDeteleUser()
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('1234567890'),
        ]);
        $result = $user->delete();
        $this->assertEquals(true, $result);
    }

    // test return 'auth.login'
    public function testUserReturnViewLogin()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login')->assertSee('login');
    }

    // test param register
    public function testUserregister()
    {
        $param = [
            'name' => 'binhdv',
            'email' => 'binhdv@gmail.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];
        $this->postJson('/register')->assertStatus(422); // nếu mà chỉ gửi request nhưng không có data thì sẽ báo lỗi
        $this->postJson('/register', $param)->assertStatus(201); //-> success
    }

    //test register nhập password < 8 ký tự => thông báo messeger
    public function testValidatePasswordRegister()
    {
        $param = [
            'name' => 'binhdv',
            'email' => 'binhdv@gmail.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ];
        $this->post('/register', $param)->assertStatus(302);
        $errors = session('errors');
        $this->assertTrue($errors->get('password')[0] == "The password must be at least 8 characters.");
    }

    //test register nhập password null => thông báo messeger
    public function testValidatePasswordRegisterNull()
    {
        $param = [
            'name' => 'binhdv',
            'email' => 'binhdv@gmail.com',
            'password' => '',
            'password_confirmation' => '',
        ];
        $this->post('/register', $param)->assertStatus(302);
        $errors = session('errors');
        $this->assertTrue($errors->get('password')[0] == "The password field is required.");
    }

    //test register nhập email null => thông báo messeger
    public function testValidateEmailRegisterNull()
    {
        $param = [
            'name' => 'binhdv',
            'email' => '',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];
        $this->post('/register', $param)->assertStatus(302);
        $errors = session('errors');
        $this->assertTrue($errors->get('email')[0] == "The email field is required.");
    }
    //test register nhập email không hợp lệ => thông báo messeger
    public function testValidateEmailRegister()
    {
        $param = [
         'name' => 'binhdv',
         'email' => 'binhdv',
         'password' => '12345678',
         'password_confirmation' => '12345678',
        ];
        $this->post('/register', $param)->assertStatus(302);
        $errors = session('errors');
        $this->assertTrue($errors->get('email')[0] == "The email must be a valid email address.");
    }

    //test register user
    public function testCanRegister()
    {
        $this->assertGuest();
        $user = User::factory()->create();
        $response = $this->post('/register', [
        'name' => $user->name,
        'email' => $user->email,
        'password' => '12345678',
        'password_confirmation' => '12345678',
        ]);
        $response->assertStatus(302)->assertSessionHasErrors('email');
        $this->withoutMiddleware()->assertGuest();
    }

    //test đăng nhập thành công
    public function testSuccessfulLogin()
    {
        User::factory()->create([
           'email' => 'binhdv@test.com',
           'password' => bcrypt('12345678'),
        ]);
        $loginData = ['email' => 'binhdv@test.com', 'password' => '12345678'];

        $this->post('/login',$loginData)->assertStatus(302)
        ->assertRedirect('/home');
        $this->assertAuthenticated();
    }

     //kiểm tra không thể đăng nhập bằng mật khẩu chính xác
    public function testUserCannotLoginWithIncorrectPassword()
    {
        $user = User::factory()->create([
            'password' => bcrypt('12345678'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => '123456789',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }
}
