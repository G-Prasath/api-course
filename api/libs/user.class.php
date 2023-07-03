<?php
require_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/dbConnection.class.php");
require $_SERVER['DOCUMENT_ROOT'].'/apis/vendor/autoload.php';

class user{
    private $db;
    private $user;

    public function __construct($username){
        $this->username = $username;
        $this->db = dbconnection::getConnection();
         
        $query = "SELECT * FROM auth WHERE username = '$this->username' OR email = '$this->username'";
        $result = mysqli_query($this->db, $query);
        if(mysqli_num_rows($result) == 1){
            $this->user = mysqli_fetch_assoc($result);
        }
        else{
            throw new Exception("User Not Found");
        }

    }

    public function getUserName(){
        return $this->user['username'];
    }
    public function getPasswordHash(){
        return $this->user['password'];
    }
    public function getEmail(){
        return $this->user['email'];
    }
    public function isActive(){
        return $this->user['active'];
    }
}


?>