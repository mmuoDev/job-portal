<?php
//Define class for database connection (PDO)
class Connection{
    //declare variables
    private $dbhost, $dbuser, $dbpass, $dbname;
//    private $dbhost = 'localhost';
//    private $dbuser = "root";
//    private $dbpass = "password";
//    private $dbname = "job_portal_api";
    //constructor
    function __construct()
    {
        $this->dbhost = getenv('db_host');
        $this->dbuser = getenv('db_user');
        $this->dbpass = getenv('db_pass');
        $this->dbname = getenv('db_name');
    }
    //connection method
    function connect(){
        try{
            $connection_string = "mysql:host=$this->dbhost;dbname=$this->dbname";
            $pdo = new PDO ($connection_string, $this->dbuser, $this->dbpass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $pdo;
            //echo "connected";
        }catch(PDOException $exception){
            $error = "Connected failed:". $exception.getMessage();
            echo encodeJson($error);
        }
    }
}