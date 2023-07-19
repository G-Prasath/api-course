<?php

include_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/login.class.php");

${basename(__FILE__, ".php")} = function(){
    if($this->get_request_method() == "POST" and isset($this->_request['username']) and isset($this->_request['password'])){
        $username = $this->_request['username'];
        $password = $this->_request['password'];
        try{
            $login = new Login($username, $password);
            $data = [
                "error" => "Login Success",
                "tokens" => $login->getLoginTokens()
            ];
            $data = $this->json($data);
            $this->response($data, 200);
        }
        catch(Exception $e){
            $data = [
                "error" => $e->getMessage()
            ];
            $data = $this->json($data);
            $this->response($data, 406);
        }
    }
    else{
        $data = [
            "error" => "Unable To Login Bad Request"
        ];
        $data = $this->json($data);
        $this->response($data, 400);
    }
};

?>