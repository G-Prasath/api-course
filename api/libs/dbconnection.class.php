<?php

class dbConnection{

    static $db = NULL;

    public static function getConnection(){
        $config_json = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/../config.json');
        $config = json_decode($config_json, true);
        
        if (dbConnection::$db != NULL) {
            return dbConnection::$db;
        } else {
            dbConnection::$db = mysqli_connect($config['db_server'],$config['db_user'],$config['db_password'], $config['db_name']);
            if (!dbConnection::$db) {
                die("Connection failed: ".mysqli_connect_error());
            } else {
                return dbConnection::$db;
            }
        }
    }


}




?>