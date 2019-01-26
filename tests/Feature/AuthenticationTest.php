<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\AttachesJWT;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations, AttachesJWT;

    protected $user;

    protected $authRoutes;

    public function setUp()
    {
        parent::setUp();

        $this->authRoutes = [
            'api/logout'
        ];
        $this->user = factory(User::class)->create(['password' => '123456']);
    }

    /** @test */
    public function user_can_register()
    {
        $response = $this->json('POST', 'api/register', [
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
        $response = $this->json('POST', 'api/login', [
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
        $response = $this->json('POST', 'api/login', [
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
        $this->loginAs($this->user);

        $response = $this->call(
            'POST',
            'api/logout',
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
            'api/logout',
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
