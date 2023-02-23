<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{

    /**
     * Handle success response 
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $code = 200)
    {
        $response = [
            'success' => true,
            'message' => $message
        ];
        if (count($result)) {
            $response['data'] = $result;
        }
        return response()->json($response, $code);
    }

    /**
     * Handle error response
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error
        ];

        if (! empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }
        if ($code === 401) {
            return response()->json($response, $code)->header('WWW-Aunthenticate', 'Token is not valid');
        }
        return response()->json($response, $code);
    }
}
