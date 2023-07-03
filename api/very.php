
<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/apis/api/libs/signup.class.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/apis/api/libs/dbconnection.class.php';

$token = mysqli_real_escape_string(dbConnection::getConnection(), $_GET['token']);


try {
    if(signup::verifyAccount($token)){
        ?>
            <h1>Success</h1>
        <?php
        }
        else{
            ?>
            <h1>Failed</h1>
        <?php
        }
} catch (Exception $e) {
    ?>
    <h1>Already exist</h1>
 <?php   
}

?>