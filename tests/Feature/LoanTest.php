<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
    public function testCreateLoan()
    {
        $token = $this->authenticate();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '. $token,
        ])->json('POST','api/loan',[
            'amount'=> 500,
            'terms'=> 5
        ]);
        
        //Write the response in laravel.log
        \Log::info(1, [$response->getContent()]);
        
        $response->assertStatus(201);
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
    }
    
    /**
     * test loan reapayments.
     *
     * @return void
     */
    public function testLoanRepayments()
    {
        $token = $this->authenticate();
        
        $loan = $this->getUnitTestUserLoan();
        
        if($loan->total_terms >= 2){
            $repaymentAmount = $loan->amount / 2;
        
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '. $token,
            ])->json('PUT','api/loan/'.$loan->id.'/repayment',['amount'=>$repaymentAmount]);
            
            //Write the response in laravel.log
            \Log::info(1, [$response->getContent()]);
            
            $response->assertStatus(200);
            
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '. $token,
            ])->json('PUT','api/loan/'.$loan->id.'/repayment',['amount'=>$repaymentAmount]);
            
            //Write the response in laravel.log
            \Log::info(1, [$response->getContent()]);
            
            $response->assertStatus(200);
        
        }else{
            $response = $this->withHeaders([
                'Authorization' => 'Bearer '. $token,
            ])->json('PUT','api/loan'.$loan->id.'/repayment',['amount'=>$loan->amount]);
            
            //Write the response in laravel.log
            \Log::info(1, [$response->getContent()]);
            
            $response->assertStatus(200);
        }
    }
}
