<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    
    public function testRegister()
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
    
    public function testLogin()
    {
        // Creating Users
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
        
        // Determine whether the login is successful and receive token
        $response->assertStatus(200);
        
        $this->assertArrayHasKey('token',$response->json()['data']);
        
        User::where('email',$email)->delete();
        
        
    }

}
