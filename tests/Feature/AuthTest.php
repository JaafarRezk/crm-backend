<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_login_updates_last_login()
    {
        $payload = [
            'name'=>'Test User',
            'email'=>'test@example.com',
            'password'=>'password',
            'password_confirmation'=>'password'
        ];
        $this->postJson('/api/register',$payload)->assertStatus(201)->assertJsonStructure(['data'=>['access_token']]);

        $this->postJson('/api/login',['email'=>'test@example.com','password'=>'password'])
            ->assertStatus(200)
            ->assertJsonStructure(['data'=>['access_token','user']]);

        $user = User::where('email','test@example.com')->first();
        $this->assertNotNull($user->last_login);
    }

    public function test_admin_can_create_user()
    {
        $admin = User::factory()->create(['user_type'=>'admin']);
        $this->actingAs($admin,'api');

        $payload = [
            'name'=>'New User',
            'email'=>'newuser@example.com',
            'password'=>'password',
            'password_confirmation'=>'password',
            'user_type'=>'sales_rep'
        ];

        $this->postJson('/api/users',$payload)->assertStatus(201);
        $this->assertDatabaseHas('users',['email'=>'newuser@example.com','user_type'=>'sales_rep']);
    }
}
