<?php 

    class DbConnect{

        private $con; 

        function connect(){

            include_once dirname(__FILE__)  . '/Constants.php';

      

            $this->con = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

            //mysqli_query($con, "SET CHARACTER SET 'utf8'");
            //mysqli_query($con, "SET NAMES 'utf8'");
            //mysqli_query($con, "SET SESSION collation_connection = 'utf8_general_ci'");
            
            if(mysqli_connect_errno()){
                echo "Failed  to connect " . mysqli_connect_error(); 
                return null; 
            }

            return $this->con; 
        }

    }