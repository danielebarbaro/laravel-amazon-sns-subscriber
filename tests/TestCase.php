<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

abstract class TestCase extends BaseTestCase
{
    protected $headers = ['Accept' => 'application/json'];

    use CreatesApplication;

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $applicationJson = $this->transformHeadersToServerVars($this->headers);
        $server = array_merge($server, $applicationJson);
        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function actingAs(UserContract $user, $driver = null)
    {
        $token = \JWTAuth::fromUser($user);
        \JWTAuth::setToken($token);
        $this->headers['Authorization'] = 'Bearer ' . $token;

        return $this;
    }
}
