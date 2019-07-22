<?php 

    class DbOperations{

        private $con; 

        function __construct(){
            require_once dirname(__FILE__) . '/DbConnect.php';
            $db = new DbConnect; 
            $this->con = $db->connect(); 
        }

        public function createUser($email, $password, $name){
           if(!$this->isEmailExist($email)){
                $stmt = $this->con->prepare("INSERT INTO users (email, password, username) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $email, $password, $name);
                if($stmt->execute()){
                    return USER_CREATED; 
                }else{
                    return USER_FAILURE;
                }
           }
           return USER_EXISTS; 
        }

        public function userLogin($email, $password){
            if($this->isEmailExist($email)){
                $hashed_password = $this->getUsersPasswordByEmail($email); 
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH; 
                }
            }else{
                return USER_NOT_FOUND; 
            }
        }

        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
        }

        public  function getUserIdByEmail($email){
            $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($id);
            $stmt->fetch(); 
            return $id;
        }

        public function getAllUsers(){
            $stmt = $this->con->prepare("SELECT id, email, username FROM users;");
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $name);
            $users = array(); 
            while($stmt->fetch()){ 
                $user = array(); 
                $user['id'] = $id; 
                $user['email']=$email; 
                $user['name'] = $name;  
                array_push($users, $user);
                //print_r($user['id']);

            }             
            return $users; 
        }


        public function getAllMessages(){
            $stmt = $this->con->prepare("SELECT message, customer FROM messages;");
            $stmt->execute(); 
            $stmt->bind_result($message, $customer);
            $messages = array(); 
            
            while($stmt->fetch()){ 
                $oneMessage = array('message1' => $message,
                                    'customer'=> $customer );
                array_push($messages, $oneMessage);
             
            }
            

            return $messages; 
        }

        

        public function getUserMessages($username){
            $stmt = $this->con->prepare("SELECT message ,in_sync, customer FROM messages WHERE customer = ? ");
            $stmt->bind_param("s" , $username);
            $stmt->execute();
            $stmt->bind_result($message , $in_sync, $customer);
            $allMessages = array();
            while ($stmt->fetch()) {
                $aMessage['message'] = $message;
                $aMessage['sync'] = $in_sync;
                $aMessage['customer'] = $customer;
                array_push($allMessages, $aMessage);
            }
            //print_r($allMessages);
            return $allMessages;
        }

        public function getUserByEmail($email){
            $stmt = $this->con->prepare("SELECT id, email, username FROM users WHERE email = ?");   
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $name);
            $stmt->fetch(); 
            $user = array(); 
            $user['id'] = $id; 
            $user['email']=$email; 
            $user['name'] = $name;  
            return $user; 
        }

        public  function updateUserSinchronization($username){
            $stmt = $this->con->prepare("UPDATE messages SET in_sync = 1 WHERE (in_sync = 0) and (customer = ?)");
            $stmt->bind_param("s", $username);
            if ($stmt->execute()) {
                return true;
            }else{
                return false;
            }
        }

        public function updateUser($email, $name,  $id){
            $stmt = $this->con->prepare("UPDATE users SET email = ?, username = ? WHERE id = ?");
            $stmt->bind_param("ssi", $email, $name, $id);
            if($stmt->execute())
                return true; 
            return false; 
        }

        public function updatePassword($currentpassword, $newpassword, $email){
            $hashed_password = $this->getUsersPasswordByEmail($email);
            
            if(password_verify($currentpassword, $hashed_password)){
                
                $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt = $this->con->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss",$hash_password, $email);

                if($stmt->execute())
                    return PASSWORD_CHANGED;
                return PASSWORD_NOT_CHANGED;

            }else{
                return PASSWORD_DO_NOT_MATCH; 
            }
        }

        public function deleteUser($id){
            $stmt = $this->con->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute())
                return true; 
            return false; 
        }

        private function isEmailExist($email){
            $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }
    }