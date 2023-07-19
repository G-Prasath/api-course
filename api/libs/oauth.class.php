<?php
require_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/dbConnection.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/user.class.php");
include_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/login.class.php");

class OAuth{
    private $db;
    private $access_token;
    private $refersh_token;
    private $valid_for;
    private $username;


    public function __construct($username, $refersh_token = NULL){
        $this->username = $username;
        $this->refersh_token = $refersh_token;
        $this->db = dbConnection::getConnection();
        $u = new user($username);
    }

    public function newSession($valid_for = 7200){
        $this->valid_for = $valid_for;
        $this->access_token = Login::generateRandomToken(16);
        $this->refersh_token = Login::generateRandomToken(16);
        $query = "INSERT INTO `apis`.`session` (`username`, `access_token`, `refersh_token`, `valid_for`, `referance_token`) VALUES ('$this->username', '$this->access_token', '$this->refersh_token', $this->valid_for, 'auth_grand');";
        if(mysqli_query($this->db, $query)){
            return array(
                'access_token' => $this->access_token,
                'valid_for' => $valid_for,
                'refersh_toke' => $this->refersh_token,
                'type' => 'api'
            );
        }
        else{
            throw new Exception("Unable to Create Token");
        }
    }

    public function refershAccess(){
        if($this->refersh_token){
            $query = "SELECT * FROM apis.session WHERE refersh_token = '$this->refersh_token'";
            $result = mysqli_query($this->db, $query);
            if($result){
                $data = mysqli_fetch_assoc($result);
                if($data['valid_for'] == 1){

                }
                else{
                    throw new Exception("Expired Token");
                }
            }
            else{
                throw new Exception("Invalid Request");
            }
        }
    }
}




?>