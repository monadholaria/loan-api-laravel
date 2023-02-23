<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Loan;
use App\Models\User;

class LoanTest extends TestCase
{
    /**
     * Authenticate user.
     *
     * @return $token
     */
    protected function authenticate()
    {
        if(!$this->getUnitTestUser()){
            User::create([
                'name' => 'Unit Test',
                'email'=> 'unit@test.com',
                'password' => bcrypt('Test@123')
            ]);
        }
        
        if (!auth()->attempt(['email'=>'unit@test.com', 'password'=>'Test@123'])) {
            return response()->json('Unauthorised', 401);
        }
        
        return auth()->user()->createToken('loans')->plainTextToken;
    }
    
    /**
     * Authenticate user.
     *
     * @return $token
     */
    protected function adminAuthenticate()
    {
        
        if (!auth()->attempt(['email'=>'monadholaria@gmail.com', 'password'=>'Mona@123'])) {
            return response()->json('Unauthorised', 401);
        }
        
        return auth()->user()->createToken('loans')->plainTextToken;
    }
    
    /**
     * Get Test user
     *
     * @return User
     */
    protected function getUnitTestUser(){
        return User::where('email','unit@test.com')->first();
    }
    
    /**
     * Get Test user's loan
     *
     * @return Loan
     */
    protected function getUnitTestUserLoan(){
        $user = $this->getUnitTestUser();
        return Loan::where('user_id',$user->id)->where('status','APPROVED')->first();
    }
    
    /**
     * test create loan.
     *
     * @return void
     */
    public function testRequiredFieldCreateLoan()
    {
        $token = $this->authenticate();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('POST','api/loan',[]);
        
        //Write the response in laravel.log
        \Log::info(1, [$response->getContent()]);
        
        $response->assertStatus(400);
        $response->assertJson([
            "success"=> false,
            "message"=> "Validation errors",
            "data"=> [
                "amount"=> [
                    "The amount field is required."
                ],
                "terms"=> [
                    "The terms field is required."
                ]
            ]
        ]);
    }
    
    /**
     * test create loan.
     *
     * @return void
     */
    public function testFieldCreateLoan()
    {
        $token = $this->authenticate();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('POST','api/loan',["amount"=>'abc',"terms"=>"xyz"]);
        
        //Write the response in laravel.log
        \Log::info(1, [$response->getContent()]);
        
        $response->assertStatus(400);
        $response->assertJson([
            "success"=> false,
            "message"=> "Validation errors",
            "data"=> [
                "amount"=> [
                    "The amount must be a number."
                ],
                "terms"=> [
                    "The terms must be a number."
                ]
            ]
        ]);
    }
    /**
     * test create loan.
     *
     * @return void
     */
    public function testSuccessCreateLoan()
    {
        $token = $this->authenticate();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('POST','api/loan',["amount"=>100,"terms"=>5]);
        
        //Write the response in laravel.log
        \Log::info(1, [$response->getContent()]);
        
        $response->assertStatus(201);
        $response->assertJson([
            "success"=> true,
            "message"=> "Loan created successfully.",
        ]);
    }
    
    /**
     * test loan approval.
     *
     * @return void
     */
    public function testAprroveLoan()
    {
        $token = $this->adminAuthenticate();
        
        $cust = $this->getUnitTestUser();
        $loan = Loan::create(['amount'=>300,'remain_amount'=>300,'total_terms'=>3,'remain_terms'=>3,'user_id'=>$cust->id]);
        
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('PUT','/api/loan/'.$loan->id.'/approval',[]);
        
        //Write the response in laravel.log
        \Log::info(1, [$response->getContent()]);
        
        $response->assertStatus(200);
        $response->assertJson([
            "success"=> true,
            "message"=> "Loan approved and scheduled repayment successfully.",
        ]);
    }
    
    /**
     * test loan approval.
     *
     * @return void
     */
    public function testAprrovedApproveLoan()
    {
        $token = $this->adminAuthenticate();
        
        $cust = $this->getUnitTestUser();
        $loan = Loan::where('status','APPROVED')->get()->first();
        
        if($loan){
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '. $token,
            ])->json('PUT','/api/loan/'.$loan->id.'/approval',[]);
            
            //Write the response in laravel.log
            \Log::info(1, [$response->getContent()]);
            
            $response->assertStatus(400);
            $response->assertJson([
                "success"=> false,
                "message"=> "Validation Error.",
                "data"=> [
                    "error"=> "Loan already approved."
                ]
            ]);
        }
    }
    
    /**
     * test loan approval.
     *
     * @return void
     */
    public function testPaidApproveLoan()
    {
        $token = $this->adminAuthenticate();
        
        $loan = Loan::where('status','PAID')->get()->first();
        
        if($loan){
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '. $token,
            ])->json('PUT','/api/loan/'.$loan->id.'/approval',[]);
            
            //Write the response in laravel.log
            \Log::info(1, [$response->getContent()]);
            
            $response->assertStatus(400);
            $response->assertJson([
                "success"=> false,
                "message"=> "Validation Error.",
                "data"=> [
                    "error"=> "Loan already paid."
                ]
            ]);
        }
    }

}
