<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoanCreateRequest;
use App\Models\Loan;
use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\AmountRequest;
use App\Services\LoanService;

class LoansController extends BaseController
{

    /**
     *
     * @OA\Post(
     *      path="/loan",
     *      operationId="create",
     *      tags={"Projects"},
     *      summary="API to create loan for autenticated user",
     *      security={ * {"sanctum": {}}, * },
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="To request a loan, Pass loan amount and number of terms",
     *          @OA\JsonContent(
     *              required={"amount","terms"},
     *              @OA\Property(property="amount", type="double", format="numeric", example="300"),
     *              @OA\Property(property="terms", type="integer", format="numeric", example="3"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful loan created"
     *       ),
     *       @OA\Response(
     *          response=400,
     *          description="Fields validation errors."
     *       ),
     *       @OA\Response(
     *          response=401,
     *          description="User not authorized"
     *       )
     *
     *   )
     */
    public function create(LoanCreateRequest $request)
    {
        $result = (new LoanService())->createLoan($request->all());
        if ($result['success']) {
            return $this->sendResponse($result['data'], $result['message'], $result['code']);
        } else {
            return $this->sendError($result['message'], $result['data'], $result['code']);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="loan/{loan}/approval",
     *      operationId="approve",
     *      tags={"Projects"},
     *      summary="Approve loan",
     *      description="Approve loan by admin user",
     *      security={ * {"sanctum": {}}, * },
     *      @OA\Parameter(
     *          name="loan",
     *          description="Loan id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Loan id does not exist"
     *       ),
     *       @OA\Response(
     *          response=401,
     *          description="User not authorized"
     *       )
     *   )
     */
    public function approve(Loan $loan)
    {
        $result = (new LoanService())->approveLoan($loan);
        if ($result['success']) {
            return $this->sendResponse($result['data'], $result['message'], $result['code']);
        } else {
            return $this->sendError($result['message'], $result['data'], $result['code']);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="loan/{loan}/repayment",
     *      operationId="repayment",
     *      tags={"Projects"},
     *      summary="To pay scheduled repayments",
     *      security={ * {"sanctum": {}}, * },
     *      @OA\Parameter(
     *          name="loan",
     *          description="Loan id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Loan id does not exist"
     *       ),
     *       @OA\Response(
     *          response=401,
     *          description="User not authorized"
     *       )
     *   )
     */
    public function repayment(AmountRequest $request, Loan $loan)
    {
        $result = (new LoanService())->manageRepayment($request->all(), $loan);
        if ($result['success']) {
            return $this->sendResponse($result['data'], $result['message'], $result['code']);
        } else {
            return $this->sendError($result['message'], $result['data'], $result['code']);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/user/{user}/loans",
     *      operationId="userLoan",
     *      tags={"Projects"},
     *      summary="Get loan information of user",
     *      description="Returns loan detail of user",
     *      security={ * {"sanctum": {}}, * },
     *      @OA\Parameter(
     *          name="user",
     *          description="user id",
     *          required=true,
     *          in="path",
     *          example="3",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *       ),
     *       @OA\Response(
     *          response=401,
     *          description="User not authorized"
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="User id does not exist"
     *       ),
     *   )
     */
    public function userLoan(User $user)
    {
        $result = (new LoanService())->userLoan($user);
        if ($result['success']) {
            return $this->sendResponse($result['data'], $result['message'], $result['code']);
        } else {
            return $this->sendError($result['message'], $result['data'], $result['code']);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/loans",
     *      operationId="loanList",
     *      tags={"Projects"},
     *      summary="Get all loans",
     *      description="For admin: Returns all loans with user detail. For customer: Return all customer specific loans",
     *      security={ * {"sanctum": {}}, * },
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *       ),
     *       @OA\Response(
     *          response=401,
     *          description="User authorization fail."
     *       )
     *   )
     */
    public function loanList()
    {
        $result = (new LoanService())->loanList();
        if ($result['success']) {
            return $this->sendResponse($result['data'], $result['message'], $result['code']);
        } else {
            return $this->sendError($result['message'], $result['data'], $result['code']);
        }
    }
}
