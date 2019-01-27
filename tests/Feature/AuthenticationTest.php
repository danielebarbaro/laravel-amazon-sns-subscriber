<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create(['password' => '123456']);
    }

    /** @test */
    public function user_can_register()
    {
        $response = $this->json('POST', route('api.auth.register'), [
            'name' => 'Test REGISTER ',
            'email' => 'test_register@example.com',
            'password' => '123456'
        ], ['Accept' => 'application/json']);


        $response->assertExactJson([
            'status' => 'success',
            'data' => [
                'email' => 'test_register@example.com',
            ]
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function user_can_login()
    {
        $response = $this->json('POST', route('api.auth.login'), [
            'email' => $this->user->email,
            'password' => '123456'
        ], ['Accept' => 'application/json']);

        $response->assertJsonStructure([
            'status',
            'data' => [
                'access_token',
                'token_type',
                'expires_in_minutes',
                'refresh_in_minutes',
            ]
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_not_login()
    {
        $response = $this->json('POST', route('api.auth.login'), [
            'email' => $this->user->email,
            'password' => 'WRONG PASSWORD'
        ], ['Accept' => 'application/json']);

        $response->assertJsonStructure([
            'status',
            'message'
        ]);

        $response->assertExactJson([
            'status' => 'error',
            'message' => 'User not found'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_logout()
    {
        $this->actingAs($this->user);

        $response = $this->call(
            'POST',
            route('api.auth.logout'),
            [
                'email' => $this->user->email,
                'password' => '123456'
            ]
        );

        $response->assertJsonStructure([
            'status',
            'message'
        ]);

        $response->assertExactJson([
            'status' => 'success',
            'message' => 'User logged out.'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_not_logout()
    {
        $response = $this->json(
            'POST',
            route('api.auth.logout'),
            [],
            ['Accept' => 'application/json']
        );

        $response->assertJsonStructure([
            'status',
            'message'
        ]);

        $response->assertStatus(401);
    }
}
