<?php

require_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/dbConnection.class.php");
require $_SERVER['DOCUMENT_ROOT'].'/apis/vendor/autoload.php';

class signup{

    private $username;
    private $email;
    private $password;

    private $db;

    public function __construct($username, $password, $email){
        $this->db = dbconnection::getConnection();
        $this->useername = $username;
        $this->email = $email;
        $this->password = $password;
        $byte = random_bytes(16);
        $this->token = $token = bin2hex($byte);

        $password = $this->hashPassword();

        //Homework make a proper workflow user already exist or not

        $queryChk = "SELECT * FROM `auth`";
        $result = mysqli_query($this->db, $queryChk);

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                if($row["email"] == $email){
                    throw new Exception("User Already Exisied");
                }
            }
        }
        $query = "INSERT INTO `auth` (`username`, `email`, `password`, `active`, `token`) VALUES ('$username', '$email', '$password', 0, '$token')";
        if(!mysqli_query($this->db, $query)){
            throw Exception("Unable To signup");
        }
        else{
            $this->id = mysqli_insert_id($this->db);
            $this->senderVerification();
        }
    }

    public function getInsertId(){
        return $this->id;
    }

    public function hashPassword(){
        $options = [
            'cost' => 12
        ];
        return password_hash($this->password, PASSWORD_BCRYPT, $options);
    }
    
    public function senderVerification(){
        $config_json = file_get_contents('../../../config.json');
        $config = json_decode($config_json, true);
        $token = $this->token;
        $email = new \SendGrid\Mail\Mail();

        $email->setFrom("noreplay@smartroofings.in", "Smartroofings Enquiry");
        $email->setSubject("It's Just For Testing Purpose");
        $email->addTo($this->email, $this->username);
        $email->addContent("text/plain", "Please Verify Your Account at : http://localhost/apis/verify?token=$token");
        $email->addContent("text/html", "
        <strong>Please Verify Your Account at : <a href=\"http://localhost/apis/verify?token=$token\">Click Here</a> </strong>"
        );
        $sendgrid = new \SendGrid($config['sendgrid']);

        try {
            $response = $sendgrid->send($email);
            echo $response->statusCode() . "\n";
        } catch (Exception $e) {
            echo 'Caught exception: '. $e->getMessage() ."\n";
        }
    }

    public static function verifyAccount($token){
           $query = "SELECT * FROM apis.auth WHERE token='$token'";
           $db = dbconnection::getConnection();
           $result = mysqli_query($db, $query);
           if($result and mysqli_num_rows($result) == 1){
                $data = mysqli_fetch_assoc($result);
                if($data['active'] == 1){
                    throw new Exception("Already Verified");
                }
                mysqli_query($db, "UPDATE `apis`.`auth` SET `active` = 1 WHERE (`token` = '$token');");
                return true;
           }
           else{
                return false;
           }
    }


}




?>