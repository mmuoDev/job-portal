<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

//ensure apiKey is passed to login and registration routes
$apiAuth = function ($request, $response, $next){
    $apiKey =getenv('apiKey');
    $headers = $request->getHeader('apiKey'); //get apiKey from the header
    $contentType = $request->getContentType();
    //check for apikey presence
    if($contentType != "application/json"){
        $data = [
            'code' => '403',
            'message' => 'Content type must be application/json'];
        return $response->withJson($data);
    }
    elseif(empty($headers[0])){
        $data = [
            'code' => 201,
            'message' => 'API key not found!'];
        return $response->withJson($data);
    }elseif ($headers[0] !== $apiKey){ //if present, ensure it matches with our apiKey
        $data = [
            'code' => 201,
            'message' => 'API key is wrong!'];
        return $response->withJson($data);
    }

    $response = $next($request, $response);
    return $response;
};

//ensure tokens are passed to other routes
$auth = function ($request, $response, $next) {
    $key =getenv('apiKey');
    $dhb = new Models();
    $headers = $request->getHeader('token');
    $header = $headers[0];
    $count = $dhb->checkToken($header); //check if token exists in table
    $token = Utilities::decrypt($header, getenv('apiKey')); //decrypt token to expose api
    $array = explode('|', $token);
    $apiKey = $array[0]; //get the apiKey

    $contentType = $request->getContentType();
    if($contentType != "application/json"){
        $data = [
            'code' => '403',
            'message' => 'Content type must be application/json'];
        return $response->withJson($data);
    }
    else if(empty($header) || $count != 1){ //check for token presence
        $data = [
            'code' => 403,
            'message' => 'Token not found'
        ];
        return $response->withJson($data);
    }elseif ($apiKey !== $key){ //check if apiKey is correct
        $data = [
            'code' => 403,
            'message' => 'Token is corrupt'
        ];
        return $response->withJson($data);
    }
    $response = $next($request, $response);

    return $response;
};