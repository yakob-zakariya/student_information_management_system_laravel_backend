<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Models\User;

use App\Traits\ApiResponse;



class AuthController extends Controller
{
    use ApiResponse;
    public function login(Request $request)
    {

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {

            $errors =
                ['email' => [
                    'The provided credentials are incorrect..'
                ]];

            return $this->errorResponse(
                $message = 'The Provided credentials are incorrect.',
                $errors,
                422
            );
            return response([
                'message' => 'The provided credentials are incorrect.',
                'errors' =>
                ['email' => [
                    'The provided credentials are incorrect.'
                ]]
            ], 422);
        }




        $user = User::where('email', $request->email)->first();

        $token = $user->createToken('auth_token')->plainTextToken;

        $data = [
            'token' => $token,
            'user' => new UserResource($user),

        ];

        return $this->successResponse(
            $data,
        );
    }

    public function getUser()
    {
        return new UserResource(Auth::user());
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response([
            'message' => 'Logged out'
        ]);
    }
}
