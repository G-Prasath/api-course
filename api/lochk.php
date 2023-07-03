
<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/apis/api/libs/dbconnection.class.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apis/api/libs/user.class.php';

try{
    $user = new user('hello');
    echo $user->getEmail();
}catch(Exception $e){
    echo $e->getMessage();
}



?>