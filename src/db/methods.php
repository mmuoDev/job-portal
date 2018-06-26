<?php
/**
 * Custom database methods
 */
require_once "connect.php";

class Models{
    //public $db = null;
    //constructor
    public function __construct()
    {
        $dhb = new Connection();
        $this->db = $dhb->connect();
    }
    //check if company's name or email already exist
    public function checkIfCompanyOrEmailExists($company_name, $email){
        $query = "SELECT count(id) as total FROM employers WHERE company_name = :company_name OR email = :email";
        $query = $this->db->prepare($query);
        $query->bindParam('company_name', $company_name);
        $query->bindParam('email', $email);
        $query->execute();
        $fetch = $query->fetch();
        $count = $fetch['total'];
        return $count;
    }
    //check if freelancer's email already exist
    public function checkIfEmailExists($email){
        $query = "SELECT count(id) as total FROM freelancers WHERE  email = :email";
        $query = $this->db->prepare($query);
        $query->bindParam('email', $email);
        $query->execute();
        $fetch = $query->fetch();
        $count = $fetch['total'];
        return $count;
    }
    public function createEmployer($company_name, $email, $password, $created_at){
        $sql = "INSERT INTO employers (`company_name`, `email`, `password`, `created_at`)
            VALUES(:company_name, :email, :password, :created_at)";
        $query = $this->db->prepare($sql);
        $query->bindParam("company_name", $company_name);
        $query->bindParam("email", $email);
        $query->bindParam("created_at", $created_at);
        $query->bindParam("password", password_hash($password, PASSWORD_DEFAULT));
        if($query->execute()){
            return $this->db->lastInsertId(); //get id of newly created user
        }else{
            return false;
        }
    }
    //create a freelancer
    public function createFreelancer($name, $email, $password, $rank_id, $created_at){
        $sql = "INSERT INTO freelancers (`name`, `email`, `password`, `rank_id`, `created_at`)
            VALUES(:name, :email, :password, :rank_id, :created_at)";
        $query = $this->db->prepare($sql);
        $query->bindParam("name", $name);
        $query->bindParam("email", $email);
        $query->bindParam("rank_id", $rank_id);
        $query->bindParam("created_at", $created_at);
        $query->bindParam("password", password_hash($password, PASSWORD_DEFAULT));
        if($query->execute()){
            return $this->db->lastInsertId(); //get id of newly created user
        }else{
            return false;
        }
    }
    //ensure created_by exists or an employer
    public function checkIfEmployerExist($user_id){
        $query = "SELECT count(id) as total FROM employers WHERE  id = :id";
        $query = $this->db->prepare($query);
        $query->bindParam('id', $user_id);
        $query->execute();
        $fetch = $query->fetch();
        $count = $fetch['total'];
        return $count;
    }
    //create a job posting
    public function createJobPosting($title, $description, $created_at, $created_by,  $status_id){
        $sql = "INSERT INTO jobs (`title`, `description`, `created_by`, `created_at`, `status_id`)
            VALUES(:title, :description, :created_by, :created_at, :status_id)";
        $query = $this->db->prepare($sql);
        $query->bindParam("title", $title);
        $query->bindParam("description", $description);
        $query->bindParam("created_at", $created_at);
        $query->bindParam("created_by", $created_by);
        $query->bindParam("status_id", $status_id);
        return $query->execute();
    }
    //fetch all published jobs
    public function getAllPublishedJobs(){
        $status_id = 1;
        $sql = "SELECT j.title, j.id as job_id, j.description, e.company_name FROM jobs as j, employers as e WHERE 
        j.created_by = e.id AND j.status_id = :status_id";
        $query = $this->db->prepare($sql);
        $query->bindParam('status_id', $status_id);
        $query->execute();
        $jobs = $query->fetchAll(PDO::FETCH_OBJ);
        return $jobs;
    }
    //if this freelancer has already submitted proposal for this particular job
    public function checkIfDuplicateProposal($job_id, $freelancer_id){
        $query = "SELECT count(id) as total FROM job_proposals WHERE  freelancer_id = :freelancer_id AND 
        job_id = :job_id";
        $query = $this->db->prepare($query);
        $query->bindParam('freelancer_id', $freelancer_id);
        $query->bindParam('job_id', $job_id);
        $query->execute();
        $fetch = $query->fetch();
        $count = $fetch['total'];
        return $count;
    }
    //get if freelancer exists in monthly limits table
    public function checkIfFreelancerExist($freelancer_id){
        $query = "SELECT count(id) as total FROM monthly_limits WHERE  freelancer_id = :freelancer_id";
        $query = $this->db->prepare($query);
        $query->bindParam('freelancer_id', $freelancer_id);
        $query->execute();
        $fetch = $query->fetch();
        $count = $fetch['total'];
        return $count;
    }
    //get freelancer's  points
    public function getFreelancerPoints($freelancer_id){
        $query = "SELECT r.limit as points FROM freelancer_ranks as r, freelancers as f WHERE  
        r.id = f.rank_id AND f.id = :freelancer_id";
        $query = $this->db->prepare($query);
        $query->bindParam('freelancer_id', $freelancer_id);
        $query->execute();
        $fetch = $query->fetch();
        $points = $fetch['points'];
        return $points;
    }
    //create a new record on monthly basis
    public function createNewRecordOnMonthlyLimit($freelancer_id, $points){
        $created_at = date('Y-m-d H:i:s');
        $sql = "INSERT INTO monthly_limits (`freelancer_id`, `points`, `created_at`)
            VALUES(:freelancer_id, :points, :created_at)";
        $query = $this->db->prepare($sql);
        $query->bindParam("points", $points);
        $query->bindParam("freelancer_id", $freelancer_id);
        $query->bindParam("created_at", $created_at);
        $query->execute();
    }
    //check if points exists for this particular month
    public function checkIfPointsExistsForThisMonth($freelancer_id){
        $current_month = date("m");
        $current_year = date("Y");
        $sql = "SELECT count(id) as total FROM monthly_limits WHERE MONTH(created_at) = :current_month AND YEAR(created_at) = :current_year AND 
        freelancer_id = :freelancer_id";
        $query = $this->db->prepare($sql);
        $query->bindParam("current_month", $current_month);
        $query->bindParam("current_year", $current_year);
        $query->bindParam("freelancer_id", $freelancer_id);
        $query->execute();
        $fetch = $query->fetch();
        $count = $fetch['total'];
        return $count;
    }
    //get points for this month
    public function getPointsForThisMonth($freelancer_id){
        $current_month = date("m");
        $current_year = date("Y");
        $sql = "SELECT points FROM monthly_limits WHERE MONTH(created_at) = :current_month AND YEAR(created_at) = :current_year AND 
        freelancer_id = :freelancer_id";
        $query = $this->db->prepare($sql);
        $query->bindParam("current_month", $current_month);
        $query->bindParam("current_year", $current_year);
        $query->bindParam("freelancer_id", $freelancer_id);
        $query->execute();
        $fetch = $query->fetch();
        $points = $fetch['points'];
        return $points;
    }
    //update points on monthly limits table
    public function updateMonthlyLimitPoints($points, $freelancer_id){
        $current_month = date("m");
        $current_year = date("Y");
        //$date = "%".$current_month."%";
        $update = "UPDATE monthly_limits SET points = :points WHERE freelancer_id = :freelancer_id AND 
         MONTH(created_at) = :current_month AND YEAR(created_at) = :current_year";
        $query = $this->db->prepare($update);
        $query->bindParam("points", $points);
        $query->bindParam("current_month", $current_month);
        $query->bindParam("current_year", $current_year);
        $query->bindParam("freelancer_id", $freelancer_id);
        $query->execute();
    }
    //monthly limit analysis
    public function checkMonthlyLimits($freelancer_id){
       $count = $this->checkIfFreelancerExist($freelancer_id); //check if freelancer already exist
       if($count > 0){
           //check if points exists for this particular month
           $check = $this->checkIfPointsExistsForThisMonth($freelancer_id);
           if($check > 0){ //points exist
               //get the current points
               $points = $this->getPointsForThisMonth($freelancer_id);
               if(!$points <= 0){
                   $curent_points = $points - 2;
                   //update points on monthly limits table
                   $this->updateMonthlyLimitPoints($curent_points, $freelancer_id);
                   return true;
               }else{
                   return false; //points exhausted for this month
               }
           }else{
               //create a new record on monthly limits
               $points = $this->getFreelancerPoints($freelancer_id); //get the points
               $curent_points = $points - 2;
               $this->createNewRecordOnMonthlyLimit($freelancer_id, $curent_points);
               return true;
           }
       }else{
           //create a new record on monthly
           $points = $this->getFreelancerPoints($freelancer_id); //get the points
           $curent_points = $points - 2;
           $this->createNewRecordOnMonthlyLimit($freelancer_id, $curent_points);
           return true;
       }
    }
    //create job proposal
    public function createJobProposal($proposal, $job_id, $freelancer_id, $created_at){
        $sql = "INSERT INTO job_proposals (`proposal`, `freelancer_id`, `job_id`, `created_at`)
            VALUES(:proposal, :freelancer_id, :job_id, :created_at)";
        $query = $this->db->prepare($sql);
        $query->bindParam("proposal", $proposal);
        $query->bindParam("job_id", $job_id);
        $query->bindParam("freelancer_id", $freelancer_id);
        $query->bindParam("created_at", $created_at);
        return $query->execute();
    }
    //get all job proposals for a particular employer
    public function getJobProposalsPerEmployer($id){
        $sql = "SELECT p.proposal, j.title as job_title, f.name as freelancer FROM job_proposals as p, jobs as j, freelancers as f
        WHERE p.freelancer_id = f.id AND p.job_id = j.id AND j.created_by = :employer_id";
        $query = $this->db->prepare($sql);
        $query->bindParam('employer_id', $id);
        $query->execute();
        $proposals = $query->fetchAll(PDO::FETCH_OBJ);
        return $proposals;
    }
    //get all proposals for a job
    public function getProposalsPerJob($job_id){
        $sql = "SELECT p.proposal,  f.name as freelancer FROM job_proposals as p, jobs as j, freelancers as f
        WHERE p.freelancer_id = f.id AND p.job_id = j.id AND j.id = :job_id";
        $query = $this->db->prepare($sql);
        $query->bindParam('job_id', $job_id);
        $query->execute();
        $proposals = $query->fetchAll(PDO::FETCH_OBJ);
        return $proposals;
    }
    //get password and id for employer
    public function getEmployerDetails($email){
        $query = "SELECT password, id from employers WHERE email = :email";
        $query = $this->db->prepare($query);
        $query->bindParam('email', $email);
        $query->execute();
        $fetch = $query->fetch();
        return $fetch;
    }
    //get password and id for freelancer
    public function getFreelancerDetails($email){
        $query = "SELECT password, id from freelancers WHERE email = :email";
        $query = $this->db->prepare($query);
        $query->bindParam('email', $email);
        $query->execute();
        $fetch = $query->fetch();
        return $fetch;
    }
    //save token
    public function saveToken($token){
        $created_at = date('Y-m-d H:i:s');
        $query = "INSERT INTO tokens (token, created_at) VALUES(:token, :created_at)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':created_at', $created_at);
        $stmt->execute();
    }
    //check token
    public function checkToken($token){
        $query = "SELECT count(*) as count FROM tokens where token = :token";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam('token', $token);
        $stmt->execute();
        $user = $stmt->fetch();
        $count = $user['count'];
        return $count;
    }
}

