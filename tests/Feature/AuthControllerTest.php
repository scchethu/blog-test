<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_registers_a_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'authorisation' => [
                    'token',
                    'type',
                ],
            ]);
    }

    /** @test */
    public function it_logs_in_a_user()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'authorisation' => [
                    'token',
                    'type',
                ],
            ]);
    }

    /** @test */
    public function it_logs_out_a_user()
    {
        $user = User::factory()->create();
        $token = auth()->guard('api')->login($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
    }

    /** @test */
    public function it_refreshes_a_user_token()
    {
        $user = User::factory()->create();
        $token = auth()->guard('api')->login($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson('/api/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'authorisation' => [
                    'token',
                    'type',
                ],
            ]);
    }
}
