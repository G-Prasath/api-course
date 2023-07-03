<?php
    error_reporting(E_ALL ^ E_DEPRECATED);
    require_once($_SERVER['DOCUMENT_ROOT']."/apis/api/REST.api.php");
    require_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/dbconnection.class.php");
    require_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/signup.class.php");
    require_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/login.class.php");
    require_once($_SERVER['DOCUMENT_ROOT']."/apis/api/libs/user.class.php");


    

    class API extends REST {

        public $data = "";

        private $db = NULL;

        private $currentCall;

        public function __construct(){
            parent::__construct();                                  // Init parent contructor
            $this->db = dbConnection::getConnection();         // Initiate Database connection  
        }

        /*
         * Public method for access api.
         * This method dynmically call the method based on the query string
         *
         */
        public function processApi(){
            $func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
            if((int)method_exists($this,$func) > 0){
                $this->$func();
            }
            else{
            // Not inside the function call using this methods
                if(isset($_GET['namespace'])){
                    $dir = $_SERVER['DOCUMENT_ROOT']."/apis/api/apis/".$_GET['namespace'];
                    $methods = scandir($dir);
                    // print_r($_SERVER);
                
                    foreach($methods as $m){
                      if($m == "." or $m == ".."){
                        continue;
                      }
                  
                      $baseem = basename($m, '.php');
                    //   echo "Try To Call $baseem and method is $func";d
                      if($baseem == $func){
                        include $dir."/".$m;
                        $this->currentCall = Closure::bind(${$baseem}, $this, get_class());
                        return $this->baseem();

                        
                      }
                    }
                }
                else{
                    $this->response($this->json(['error' => 'method not found']), 404);
                }   // If the method not exist with in this class, response would be "Page not found".
            }
        }

        public function __call($method, $args){

            // $methods = get_class_methods("API");

            
            // Inside the class functions is private and protected  get data using this senario
            // foreach($methods as $m){
            //   if($fun == $m){
            //     echo "Calling Private Function : $m";
            //     return $this->$m();
            //   }
            // }

            if(is_callable($this->currentCall)){
                return call_user_func_array($this->currentCall, $args);
            }else{
                $this->response($this->json(['error' => 'method not callable']), 404);
            }

        }

        /*************API SPACE START*******************/




        private function about(){

            if($this->get_request_method() != "POST"){
                $error = array('status' => 'WRONG_CALL', "msg" => "The type of call cannot be accepted by our servers.");
                $error = $this->json($error);
                $this->response($error,406);
            }
            $data = array('version' => '0.1', 'desc' => 'This API is created by Blovia Technologies Pvt. Ltd., for the public usage for accessing data about vehicles.');
            $data = $this->json($data);
            $this->response($data,200);

        }

        private function verify(){
            $user = $this->_request['user'];
            $password =  $this->_request['pass'];

            $flag = 0;
            if($user == "admin"){
                if($password == "adminpass123"){
                    $flag = 1;
                }
            }

            if($flag == 1){
                $data = [
                    "status" => "verified"
                ];
                $data = $this->json($data);
                $this->response($data,200);
            } else {
                $data = [
                    "status" => "unauthorized"
                ];
                $data = $this->json($data);
                $this->response($data,403);
            }
        }

        private function test(){
                $data = $this->json(getallheaders());
                $this->response($data,200);
        }

        private function get_current_user(){
            $username = $this->is_logged_in();
            if($username){
                $data = [
                    "username"=> $username
                ];
                $this->response($this->json($data), 200);
            } else {
                $data = [
                    "error"=> "unauthorized"
                ];
                $this->response($this->json($data), 403);
            }
        }

        private function logout(){
            $username = $this->is_logged_in();
            if($username){
                $headers = getallheaders();
                $auth_token = $headers["Authorization"];
                $auth_token = explode(" ", $auth_token)[1];
                $query = "DELETE FROM session WHERE session_token='$auth_token'";
                $db = $this->dbConnect();
                if(mysqli_query($db, $query)){
                    $data = [
                        "message"=> "success"
                    ];
                    $this->response($this->json($data), 200);
                } else {
                    $data = [
                        "user"=> $this->is_logged_in()
                    ];
                    $this->response($this->json($data), 200);
                }
            } else {
                $data = [
                    "user"=> $this->is_logged_in()
                ];
                $this->response($this->json($data), 200);
            }
        }

        private function user_exists(){
            if(isset($this->_request['data'])){
                $data = $this->_request['data'];
                $db = $this->dbConnect();
                $result = mysqli_query($db, "SELECT id, username, mobile FROM users WHERE id='$data' OR username='$data' OR mobile='$data'");
                if($result){
                    $result = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    $this->response($this->json($result), 200);
                } else {
                    $data = [
                        "error"=>"user_not_found"
                    ];
                    $this->response($this->json($data), 404);
                }

            } else {
                $data = [
                    "error"=>"expectation_failed"
                ];
                $this->response($this->json($data), 417);
            }
        }

        // private function signup(){
        //     if($this->get_request_method() != "POST"){
        //         $data = [
        //             "error"=>"method_not_allowed"
        //         ];
        //         $this->response($this->json($data), 405);
        //     }
        //     if(isset($this->_request['username']) and isset($this->_request['password']) and isset($this->_request['mobile'])){
        //         $username = $this->_request['username'];
        //         $password = $this->_request['password'];
        //         $mobile = $this->_request['mobile'];

        //         $query = "INSERT INTO users (username, password, mobile) VALUES ('$username', '$password', '$mobile');";

        //         $db = $this->dbConnect();
        //         $result = mysqli_query($db, $query);
        //         if($result){
        //             $data = [
        //                 "message"=>"success"
        //             ];
        //             $this->response($this->json($data), 201);
        //         } else {
        //             $data = [
        //                 "error"=>"internal_server_error"
        //             ];
        //             $this->response($this->json($data), 500);
        //         }
        //     } else {
        //         $data = [
        //             "error"=>"expectation_failed"
        //         ];
        //         $this->response($this->json($data), 417);
        //     }
        // }

        // private function login(){
        //     if($this->get_request_method() != "POST"){
        //         $data = [
        //             "error"=>"method_not_allowed"
        //         ];
        //         $this->response($this->json($data), 405);
        //     }

        //     if(isset($this->_request['username']) and isset($this->_request['password'])){
        //         $db = $this->dbConnect();
        //         $username = $this->_request['username'];
        //         $password = $this->_request['password'];
        //         $result = mysqli_query($db, "SELECT * FROM users WHERE (id='$username' OR username='$username' OR mobile='$username') AND password = '$password'");
        //         $d = mysqli_fetch_assoc($result);
        //         if($d){
        //             $userid = $d['id'];
        //             $token = $this->generate_hash();
        //             $query = "INSERT INTO `session` (session_token, is_valid, user_id) VALUES ('$token', '1', '$userid');";
        //             if(mysqli_query($db, $query)){
        //                 $data = [
        //                     "message"=>"success",
        //                     "token"=>$token
        //                 ];
        //                 $this->response($this->json($data), 201);
        //             } else {
        //                 $data = [
        //                     "error"=>"internal_server_error",
        //                     "message"=>mysqli_error($db)
        //                 ];
        //                 $this->response($this->json($data), 500);
        //             }
        //         } else {
        //             $data = [
        //                 "error"=>"invalid_credentials"
        //             ];
        //             $this->response($this->json($data), 404);
        //         }
        //     } else {
        //         $data = [
        //             "error"=>"expectation_failed"
        //         ];
        //         $this->response($this->json($data), 417);
        //     }
        // }

        function generate_hash(){
            $bytes = random_bytes(16);
            return bin2hex($bytes);
        }

        function is_logged_in(){
            $headers = getallheaders();
            if(isset($headers["Authorization"])){
                $auth_token = $headers["Authorization"];
                $auth_token = explode(" ", $auth_token)[1];

                $query = "SELECT * FROM session WHERE session_token='$auth_token'";
                $db = $this->dbConnect();
                $_result = mysqli_query($db, $query);
                $d = mysqli_fetch_assoc($_result);
                if($d){
                    $data = $d['user_id'];
                    $result = mysqli_query($db, "SELECT id, username, mobile FROM users WHERE id='$data' OR username='$data' OR mobile='$data'");
                    if($result){
                        $result = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        return $result["username"];
                    } else {
                        return false;
                    }

                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        function gen_hash(){
            if(isset($this->_request['pass'])){
                $cost = (int)$this->_request['cost'];
                $hash = password_hash($this->_request['pass'], PASSWORD_BCRYPT);
                $data = [
                    "hash" => $hash,
                    "info" => password_get_info($hash),
                    "val" => $this->_request['pass'],
                    "verify pass" => password_verify($this->_request['pass'], $hash),
                ];
                $data = $this->json($data);
                $this->response($data, 200);
            }
        }



        /*************API SPACE END*********************/

        /*
            Encode array into JSON
        */
        private function json($data){
            if(is_array($data)){
                return json_encode($data, JSON_PRETTY_PRINT);
            } else {
                return "{}";
            }
        }

    }

    // Initiiate Library

    $api = new API;
    $api->processApi();
?>