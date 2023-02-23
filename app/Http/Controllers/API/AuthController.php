<?php
namespace App\Http\Controllers\API;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends BaseController
{

    /**
     * User registration API
     *
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request)
    {
        $success = (new AuthService())->register($request->all());
        return $this->sendResponse($success, 'User register successfully.', 201);
    }

    /**
     * User authentication API
     *
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $result = (new AuthService())->login($request);
        if ($result['success']) {
            return $this->sendResponse($result['data'], $result['message']);
        } else {
            return $this->sendError($result['message'], $result['data'], $result['code']);
        }
    }
    
    /**
     * User Logout API
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        
        (new AuthService())->logout();
        return $this->sendResponse([], 'Successfully logged out.');
    }
    
}
