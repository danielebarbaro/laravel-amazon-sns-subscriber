<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AuthRequest;
use App\Http\Requests\RegisterAuthRequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use JWTAuth;

class JWTController extends Controller
{
    /**
     * @param RegisterAuthRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterAuthRequest $request)
    {
        $credentials = $request->only('name', 'email', 'password');

        try {
            $user = User::create($credentials);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => "Failed to register user, please try again. {$exception->getMessage()}"
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'email' => $user->email,
            ]
        ], 201);
    }

    /**
     * @param AuthRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(AuthRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @param AuthRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(AuthRequest $request)
    {
        $token = $request->header('Authorization');

        JWTAuth::invalidate($token);
        auth()->logout();
        return response()->json([
            'status' => 'success',
            'message' => "User logged out."
        ], 200);
    }

    /**
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in_minutes' => config('jwt.ttl') * 60,
                'refresh_in_minutes' => config('jwt.refresh_ttl') * 60
            ]
        ]);
    }
}
