<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Loan;
use Illuminate\Support\Carbon;
use App\Models\ScheduledRepayment;

class LoanService
{

    public function userLoan(User $user)
    {
        $authUser = Auth::user();
        $result = array();
        if ($authUser->role === 'ADMIN' || $authUser->id === $user->id) {
            $loans = Loan::where('user_id', $user->id)->with('scheduledRepayments')->get();
            if (! count($loans)) {
                $result['message'] = 'No Loans found.';
                $result['data']['error'] = 'No Loans found for this customer.';
            } else {
                $result['data'] = $loans;
                $result['message'] = count($loans) . ' Loans found';
            }
            $result['success'] = true;
            $result['code'] = 200;
        } else {
            $result['success'] = false;
            $result['message'] = 'Unauthorized.';
            $result['data']['error'] = 'User authorization fail.';
            $result['code'] = 401;
        }
        return $result;
    }

    public function loanList()
    {
        $user = Auth::user();
        $loans = array(); 
        if($user->role == 'ADMIN'){
            $loans = Loan::with('user')->get();
        }else{
            $loans = Loan::where('user_id',$user->id)->get();
        }
        if (! count($loans)) {
            $result['message'] = 'No Loans found.';
            $result['data']['error'] = 'No Loans found for this customer.';
        } else {
            $result['data'] = $loans;
            $result['message'] = count($loans) . ' Loans found';
        }
        $result['success'] = true;
        $result['code'] = 200;
        return $result;
    }

    public function createLoan($input)
    {
        $user = Auth::user();
        $loan = new Loan();
        $loan->amount = $loan->remain_amount = $input['amount'];
        $loan->total_terms = $loan->remain_terms = $input['terms'];
        $loan->user_id = $user->id;
        $loan->save();
        $result['success'] = true;
        $result['code'] = 201;
        $result['message'] = 'Loan created successfully.';
        $result['data'] = array();
        return $result;
    }

    public function approveLoan(Loan $loan)
    {
        if ($loan->status === 'APPROVED') {
            $result['success'] = false;
            $result['message'] = 'Validation Error.';
            $result['data']['error'] = 'Loan already approved.';
            $result['code'] = 400;
            return $result;
        } elseif ($loan->status === 'PAID') {
            $result['success'] = false;
            $result['message'] = 'Validation Error.';
            $result['data']['error'] = 'Loan already paid.';
            $result['code'] = 400;
            return $result;
        }
        $loan->status = 'APPROVED';
        $loan->save();
        $this->createRepayments($loan);
        $result['success'] = true;
        $result['code'] = 200;
        $result['message'] = 'Loan approved and scheduled repayment successfully.';
        $result['data'] = array();
        return $result;
    }

    public function createRepayments(Loan $loan)
    {
        $date = Carbon::createFromDate($loan->created_at);
        $repaymentAmount = round($loan->amount / $loan->total_terms, 5);

        for ($i = 0; $i < $loan->total_terms; $i ++) {
            $nextAt = $date->addDays(7)->format('Y-m-d');
            $repayment = new ScheduledRepayment();
            $repayment->amount = $repaymentAmount;
            $repayment->amount_to_pay = $repaymentAmount;
            $repayment->loan_id = $loan->id;
            $repayment->repayment_at = $nextAt;
            $repayment->status = 'PENDING';
            $repayment->save();
        }
    }

    public function manageRepayment($input, Loan $loan)
    {
        $user = Auth::user();
        $amount = $input['amount'];
        $result = array();
        if ($loan->status != 'APPROVED') {
            $result['success'] = false;
            $result['message'] = 'Validation Error.';
            $result['data']['error'] = 'Your loan status is: ' . $loan->status;
            $result['code'] = 400;
            return $result;
        }
        if ($amount > $loan->remain_amount) {
            $result['success'] = false;
            $result['message'] = 'Validation Error.';
            $result['data']['error'] = 'Amount is greater then total loan amount to pay. Your reamining amount to paid is: ' . $loan->remain_amount;
            $result['code'] = 400;
            return $result;
        }

        if ($user->id == $loan->user_id) {
            $scheduledRepayment = $this->getNextRepayment($loan->id);
            if ($scheduledRepayment) {
                if ($amount >= $scheduledRepayment->amount_to_pay) {
                    $this->updateRepayment($scheduledRepayment, $amount);
                    $this->checkandUpdateLoanStatus($loan);
                    $result['success'] = true;
                    $result['code'] = 200;
                    $result['message'] = 'Loan scheduled repayment paid successfully.';
                    $result['data'] = array();
                    return $result;
                } else {
                    $result['success'] = false;
                    $result['message'] = 'Repayment amount error.';
                    $result['data']['error'] = 'You are paying lesser amount then scheduled amount. Your schedueled repayment amount is: ' . $scheduledRepayment->amount_to_pay;
                    $result['code'] = 400;
                    return $result;
                }
            } else {
                $result['success'] = false;
                $result['message'] = 'Validation error.';
                $result['data']['error'] = 'Loan\'s pending scheduled repayment\'s not found.';
                $result['code'] = 400;
                return $result;
            }
        } else {
            $result['success'] = false;
            $result['message'] = 'Not Authorized.';
            $result['data']['error'] = 'You are not authorized for this loan repayment.';
            $result['code'] = 401;
            return $result;
        }
    }

    public function getNextRepayment($loanId)
    {
        $repayment = ScheduledRepayment::where('loan_id', $loanId)->where('status', 'PENDING')
            ->orderBy('repayment_at')
            ->first();
        return $repayment;
    }

    public function updateRepayment(ScheduledRepayment $scheduledRepayment, $amount)
    {
        $scheduledRepayment->amount_paid = $amount;
        $scheduledRepayment->status = 'PAID';
        $scheduledRepayment->save();

        $remain_amount = round($amount - $scheduledRepayment->amount_to_pay, 5);
        // return $remain_amount;
        if ($remain_amount) {
            $otherRepayments = ScheduledRepayment::where('loan_id', $scheduledRepayment->loan_id)->where('status', 'PENDING')->get();
            if ($otherRepayments) {
                while ($remain_amount) {
                    foreach ($otherRepayments as $repayment) {
                        if ($repayment->amount_to_pay > $remain_amount) {
                            $repayment->amount_to_pay = round($repayment->amount_to_pay - $remain_amount, 5);
                            $remain_amount = round($remain_amount - $repayment->amount_to_pay, 5);
                            $remain_amount = 0;

                            $repayment->save();
                            break;
                        } elseif ($repayment->amount_to_pay == $remain_amount) {
                            $repayment->status = 'PAID';
                            $repayment->amount_paid = $remain_amount;
                            $remain_amount = 0;
                            $repayment->save();
                            break;
                        } elseif ($repayment->amount_to_pay < $remain_amount) {
                            $repayment->amount_paid = $remain_amount;
                            $repayment->status = 'PAID';
                            $remain_amount = round($remain_amount - $repayment->amount_to_pay, 5);
                            $repayment->save();
                        }
                    }
                }
            }
        }
    }

    public function checkandUpdateLoanStatus(Loan $loan)
    {
        $repayments = ScheduledRepayment::where('loan_id', $loan->id)->where('status', 'PENDING')
            ->get()
            ->toArray();
        if (! $repayments) {
            $loan->remain_amount = 0;
            $loan->remain_terms = 0;
            $loan->status = 'PAID';
            $loan->save();
        } else {
            $remain_amount = array_sum(array_column($repayments, 'amount_to_pay'));
            $loan->remain_amount = $remain_amount;
            $loan->remain_terms = count($repayments);
            $loan->save();
        }
        
    }
}