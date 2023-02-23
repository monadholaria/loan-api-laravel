<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{

    public function register($input)
    {
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('loans')->plainTextToken;
        $success['name'] = $user->name;
        return $success;
    }

    public function login($input)
    {
        $result['success'] = false;
        if (Auth::attempt([
            'email' => $input->email,
            'password' => $input->password
        ])) {
            $user = Auth::user();
            $result['success'] = true;
            $result['data']['token'] = $user->createToken('loans')->plainTextToken;
            $result['data']['name'] = $user->name;
            $result['message'] = 'User login successfully.';
        } else {
            $result['message'] = 'Not authenticated.';
            $result['data']['error'] = 'User authentication fail.';
            $result['code'] = 401;
        }
        return $result;
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
    }
}