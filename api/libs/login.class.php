<?php
require_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/dbConnection.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/user.class.php");
require $_SERVER['DOCUMENT_ROOT'].'/apis/vendor/autoload.php';

class Login{
    private $db;
    private $isTokenAuth = false;
    private $loginToken = null;

    public function __construct($username, $password = NULL){
        $this->db = dbconnection::getConnection(); 
        if($password == NULL){
            //token Based Auth
            $this->token = $username;
            $this->isTokenAuth = true;
        }
        else{
            //password Based Auth
            $this->username = $username;  //it might be user name or email
            $this->password = $password;
        }

        if($this->isTokenAuth){
            throw new Exception("Not Implemented");
        }
        else{
            $user = new user($username);
            $hash = $user->getPasswordHash();
            $username = $user->getUserName();

            if(password_verify($password, $hash)){
                //generate Token
                if(!$user->isActive()){
                    throw new Exception("Please Check Your Email and Active Your Account");
                }
                $this->loginToken = $this->addSession();
            }
            else{
                throw new Exception("Password Miss Match");
            }
        }
    }

    public function getLoginToken(){
        return $this->loginToken;
    }

    private function addSession(){
        $token = Login::generateRandomToken(16);
        $query = "INSERT INTO `apis`.`session` (`username`, `token`) VALUES ('$this->username', '$token')";
        if(mysqli_query($this->db, $query)){
            return $token;
        }
        else{
            throw new Exception("Unable to Create Token");
        }
    }

    public static function generateRandomToken($len){
        $bytes = openssl_random_pseudo_bytes(32, $cstrong);
        return bin2hex($bytes);
    }



}




?>