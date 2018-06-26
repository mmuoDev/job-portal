<?php
/**
 * Route to create a employer
 */
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/api/jobs', function (){
    $this->post('/create', function(Request $request, Response $response) {
        $body = $request->getParsedBody();
        $title = $body['title'];
        $description = $body['description'];
        $status_id = $body['status_id'];
        $created_by = $body['created_by'];
        $created_at = date('Y-m-d H:i:s');

        $dhb = new Models();
        $check = $dhb->checkIfEmployerExist($created_by); //check if employer exists
        if($title == "" || $description == "" || $status_id == "" || $created_by == ""){
            $data = [
                'code' => 201,
                'message' => 'One or more parameter is missing'];
        }elseif ($status_id != 1 && $status_id != 2){ //status_id must be 1 or 2, publish and draft respectively
            $data = [
                'code' => 201,
                'message' => 'Wrong status id. See docs'];
        }elseif ($check == 0){
            $data = [
                'code' => 404,
                'message' => 'Employer does not exist'];
        }else{
            //create job posting
            $result = $dhb->createJobPosting($title, $description,$created_at,$created_by, $status_id);
            if($result){
                $data = [
                    'code' => 200,
                    'message' => 'Job Posted'];
            }else{
                $data = [
                    'code' => 201,
                    'message' => 'Something went wrong'];
            }
        }
        return $response->withJson($data);
    });

    //get all published jobs
    $this->get('/published', function (Request $request, Response $response){
        $dhb = new Models();
        $jobs = $dhb->getAllPublishedJobs();
        $data = [
            'code' => 200,
            'data' => $jobs
        ];
        return $response->withJson($data);
    });

    //submit proposals
    $this->post('/proposal/create', function(Request $request, Response $response) {
        $body = $request->getParsedBody();
        $proposal = $body['proposal'];
        $job_id = $body['job_id'];
        $freelancer_id = $body['freelancer_id'];
        $created_at = date('Y-m-d H:i:s');

        $dhb = new Models(); //create new object model
        $check = $dhb->checkIfDuplicateProposal($job_id, $freelancer_id);  //if this freelancer has already submitted proposal for this particular job
        if($proposal == "" || $job_id == "" || $freelancer_id == ""){
            $data = [
                'code' => 201,
                'message' => 'One or more parameter is missing'];
        }elseif ($check > 0){
            $data = [
                'code' => 201,
                'message' => 'You can no longer submit proposal for this job posting'];
        }else{
            //check monthly limits
            $check = $dhb->checkMonthlyLimits($freelancer_id);
            //var_dump($check);exit;
            //create job proposal
            if($check){
                $result = $dhb->createJobProposal($proposal, $job_id, $freelancer_id, $created_at);
                if($result){
                    $data = [
                        'code' => 200,
                        'message' => 'Job Proposal Posted'];
                }else{
                    $data = [
                        'code' => 201,
                        'message' => 'Something went wrong'];
                }
            }else{
                $data = [
                    'code' => 201,
                    'message' => 'Points exhausted'
                ];
            }

        }
        return $response->withJson($data);
    });
    //get all job proposals for a particular employer
    $this->get('/proposals/employer/{id}', function (Request $request, Response $response){
        $employer_id = $request->getAttribute('id');
        $dhb = new Models();
        $jobs = $dhb->getJobProposalsPerEmployer($employer_id);
        $data = [
            'code' => 200,
            'data' => $jobs
        ];
        return $response->withJson($data);
    });

    //get all proposals for a particular job
    $this->get('/proposals/{job_id}', function (Request $request, Response $response){
        $job_id = $request->getAttribute('job_id');
        $dhb = new Models();
        $proposals = $dhb->getProposalsPerJob($job_id);
        $data = [
            'code' => 200,
            'data' => $proposals
        ];
        return $response->withJson($data);
    });
})->add($auth);