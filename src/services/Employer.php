<?php
/**
 * Route to create a employer and login
 */
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/api/employer', function (){
    //register a new employer
    $this->post('/register', function(Request $request, Response $response){
       $body = $request->getParsedBody();
       $company_name = $body['company_name'];
       $email = $body['email'];
       $password = $body['password'];
       $created_at = date('Y-m-d H:i:s');

       $dhb = new Models();
       //check if company_name or email is already taken
        $check = $dhb->checkIfCompanyOrEmailExists($company_name, $email);
        if($company_name == "" || $email == "" || $password == ""){ //ensure all fields are filled
            $data = [
                'code' => '201',
                'message' => 'One or more parameter is missing'];
        }
        else if($check > 0){
            $data = [
                'code' => '201',
                'message' => 'Company name or email already exists'];
        }
        else{
            //create employer
            $created_id = $dhb->createEmployer($company_name, $email, $password, $created_at);
            if($created_id != false){
                $data = [
                    'code' => 200,
                    'message' => 'User created',
                    'data' => [
                        'id' => $created_id,
                        'company_name' => $company_name,
                        'created_at' => $created_at
                    ]
                ];
            }else{
                $data = [
                    'code' => 201,
                    'message' => 'Unable to create user'
                ];
            }
        }
        return $response->withJson($data);
    });
})->add($apiAuth);

//employer authentication (login)
$app->post('/api/employer/login', function(Request $request, Response $response, array $args){
    $body = $request->getParsedBody();
    $email = $body['email'];
    $password = $body['password'];
    $time = strtotime("now"); //get the time

    $dhb = new Models();
    if($email == "" || $password == ""){
        $data = [
            'code' => 201,
            'message' => "One or two parameters is missing"
        ];
    }else{
        $details = $dhb->getEmployerDetails($email);
        $password_hash = $details['password'];
        $user_id = $details['id'];
        if(password_verify($password, $password_hash)){
            //generate token = api key + user id + time
            $apiKey = getenv('apiKey');
            $generateToken = $apiKey.'|'.$user_id.'|'.$time;
            $token = Utilities::encrypt($generateToken, getenv('apiKey')); //encrypted token
            $dhb->saveToken($token); //save token
            $data =[
                'code' => 200,
                'user_id' => $user_id,
                'token' => $token,
                'message' => "Use this token for subsequent API requests",
            ];
        }else{
            $data = [
                'code' => 201,
                'message' => 'email or password is wrong!'];
        }
    }
    return $response->withJson($data);
})->add($apiAuth);