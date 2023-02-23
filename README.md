<p align="center">
<font size="10">Loan API</font>
</p>

## About API

Loan API is REST API which is built on Laravel Framework.

It is an app that allows authenticated users to go through a loan application. Customer can request a loan. Admin will aprrove the loan and after approval, custome can pay scheduled repayment. Here scheduled repayment assumed to have a “weekly” frequency.

After the loan is approved, the user must be able to submit the weekly loan repayments. It can be a simplified repay functionality, which won’t need to check if the dates are correct but will just set the weekly amount to be repaid.

Actions to implemented:
- Customer create a loan:
	- Customer submit a loan request defining amount and term
		- example:
			- Request amount of 10.000 $ with term 3 on date 7th Feb 2022
			- he will generate 3 scheduled repayments:
				- 14th Feb 2022 with amount 3.333,33 $ 
				- 21st Feb 2022 with amount 3.333,33 $ 
				- 28th Feb 2022 with amount 3.333,34 $
    - The loan and scheduled repayments will have state PENDING 

- Admin approve the loan:
Admin change the pending loans to state APPROVED 

- Customer can view loan belong to him:
Add a policy check to make sure that the customers can view them own loan only.

- Customer add a repayments:
Customer add a repayment with amount greater or equal to the scheduled repayment
The scheduled repayment change the status to PAID
If all the scheduled repayments connected to a loan are PAID automatically also the loan become PAID

## Tools and Technologies
- PHP Framework: Laravel 9.*
- PHP version: PHP 8
- Database: mysql

## Setup/Installation
Copy .env.example to .env or reaname .env.example to .env
Add Application detail in .env file

Must add database detail in .env file
- DB_CONNECTION=mysql
- DB_HOST=127.0.0.1
- DB_PORT=8889
- DB_DATABASE=
- DB_USERNAME=
- DB_PASSWORD=

Must run following command for Database setup
- For database: php artisan migrate
- To add admin user: php artisan db:seed

## API detail/documentation

To register new customer use following API

- Post - {app_base_url}/api/register
	- Request Body: {"name":"name", "email":"email@example.com", "password":"ABC123", "c_password":"ABC123"}
    - Response: {"success": true, "message": "User register successfully.","data": { "token": "token_value", "name": "name"} }

- Post - {app_base_url}/api/login
	- Request Body: {"email":"email@example.com", "password":"ABC123"}
    - Response: {"success": true, "message": "User login successfully.","data": { "token": "token_value", "name": "name"} }

- Post - {app_base_url}/api/logout
    - Token must use for header Authorizarion as Bearer: Enter token in format (Bearer {token})


- Note: To consume other then register and login Loan API, token must use for header Authorizarion as Bearer: Enter token in format (Bearer {token})

All Loan API detail is available at - {app_base_url}/api/documentation

## Test:

Some feature and unit test case are available. To run those particular test cases run following command

- ./vendor/bin/phpunit tests/Feature/AuthTest.php
- ./vendor/bin/phpunit tests/Feature/LoanTest.php
- ./vendor/bin/phpunit tests/Unit/AuthTest.php
- ./vendor/bin/phpunit tests/Unit/LoanTest.php