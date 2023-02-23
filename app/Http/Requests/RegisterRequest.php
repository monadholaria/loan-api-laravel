<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'email' => 'required|email|unique:Users',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
        ];
    }
    
    public function failedValidation(Validator $validator)
    
    {
        
        throw new HttpResponseException(response()->json([
            
            'success'   => false,
            
            'message'   => 'Validation errors',
            
            'data'      => $validator->errors()
            
        ],400));
        
    }
}
