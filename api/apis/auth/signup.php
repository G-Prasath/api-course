<?php

include_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/signup.class.php");

${basename(__FILE__, ".php")} = function(){
    // echo $this->get_request_method(); 

  if($this->get_request_method() == "POST" and isset($this->_request['username']) and isset($this->_request['password']) and isset($this->_request['email'])){
      $username = $this->_request['username'];
      $password = $this->_request['password'];
      $email = $this->_request['email'];
      try {
          $s = new signup($username, $password, $email);
          $data = [
              "message" => "Signup Success",
              "userId" => $s->getInsertId()
          ];
          $this->response($this->json($data), 200);

      } catch (Exception $e) {
          $data = [
              "error" => $e->getMessage()
          ];
          $this->response($this->json($data), 409);
      }
  }
  else{
      $data = [
          "error" => "bad request test",
          "method" => $this->get_request_method()
      ];
      $data = $this->json($data);
      $this->response($data, 400);
  }
};



?>