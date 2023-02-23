<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;


class AuthTest extends TestCase
{
    public function testRequiredFieldsForRegistration()
    {
        $response = $this->json('POST','api/register',[]);
        
        $response->assertStatus(400);
        $response->assertJson([
            "success"=> false,
            "message"=> "Validation errors",
            "data"=> [
            "name"=> [
                "The name field is required."
            ],
            "email"=> [
                "The email field is required."
            ],
            "password"=> [
                "The password field is required."
            ],
            "c_password"=> [
                "The c password field is required."
            ]
        ]
        ]);
    }

   public function testRepeatPassword()
    {
        $userData = [
            "name" => "Test cust",
            "email" => time()."test@abc.com",
            "password" => "demo12345",
            "c_password" => "demo"
        ];

        $this->json('POST', 'api/register', $userData, ['Accept' => 'application/json'])
            ->assertStatus(400)
            ->assertJson([
                "success"=> false,
                "message"=> "Validation errors",
                "data"=> [
                    "c_password"=> [
                        "The c password and password must match."
                    ]
                ]
            ]);
    }

    public function testSuccessfulRegistration()
    {
        $response = $this->json('POST', '/api/register', [
            'name'  =>  $name = 'Test',
            'email'  =>  $email = time().'test@example.com',
            'password'  =>  $password = '123456789',
            'c_password' => $password
        ]);
        
        //Write the response in laravel.log
        \Log::info(1, [$response->getContent()]);
        
        $response->assertStatus(201);
        
        // Receive our token
        $this->assertArrayHasKey('token',$response->json()['data']);
        
        User::where('email',$email)->delete();
    }
    
    public function testRequiredFieldLogin(){
        
        $response = $this->json('POST','/api/login',[]);
        $response->assertStatus(400);
        $response->assertJson([
            "success"=> false,
            "message"=> "Validation errors",
            "data"=> [
                "email"=> [
                    "The email field is required."
                ],
                "password"=> [
                    "The password field is required."
                ]
            ]
        ]);
    }
    
    public function testSuccessLogin() {
        User::create([
            'name' => 'Test',
            'email'=> $email = time().'@example.com',
            'password' => $password = bcrypt('123456789')
        ]);
        
        // Simulated landing
        $response = $this->json('POST','/api/login',[
            'email' => $email,
            'password' => '123456789',
        ]);
        
        //Write the response in laravel.log
        \Log::info(1, [$response->getContent()]);
        
        $response->assertStatus(200);
        
        // Receive our token
        $this->assertArrayHasKey('token',$response->json()['data']);
    }
}
