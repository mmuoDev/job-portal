<?php
/**
 * Route to create a freelancer and login
 */
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/api/freelancer', function (){
    //register a new employer
    $this->post('/register', function(Request $request, Response $response){
        $body = $request->getParsedBody();
        $name = $body['name'];
        $email = $body['email'];
        $password = $body['password'];
        $rank_id = $body['rank_id'];
        $created_at = date('Y-m-d H:i:s');

        $dhb = new Models();
        //check if company_name or email is already taken
        $check = $dhb->checkIfEmailExists($email);
        if($name == "" || $email == "" || $password == "" || $rank_id == ""){ //ensure all fields are filled
            $data = [
                'code' => '201',
                'message' => 'One or more parameter is missing'];
        }
        elseif ($rank_id != 1 && $rank_id != 2){ //ensure rank_id is either 1 or 2
            $data = [
                'code' => '201',
                'message' => 'Rank ID does not exist. See Docs'];
        }
        else if($check > 0){
            $data = [
                'code' => '201',
                'message' => 'Email already exists'];
        }
        else{
            //create employer
            $created_id = $dhb->createFreelancer($name, $email, $password, $rank_id, $created_at);
            if($created_id != false){
                $data = [
                    'code' => 200,
                    'message' => 'User created',
                    'data' => [
                        'id' => $created_id,
                        'name' => $name,
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
$app->post('/api/freelancer/login', function(Request $request, Response $response, array $args){
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
        $details = $dhb->getFreelancerDetails($email);
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