<?php


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

require '../includes/DbOperations.php';

$app = new \Slim\App([
    'settings'=>[
        'displayErrorDetails'=>true
    ]
]);

// $app = new \Slim\App([
//     'settings' => [
//         'displayErrorDetails' => true,
//     ],
// ]);


/* 
    endpoint: createuser
    parameters: email, password, name
    method: POST
*/
$app->post('/createuser', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('email', 'password', 'name'), $request, $response)){

        $request_data = $request->getParsedBody(); 

        $email = $request_data['email'];
        $password = $request_data['password'];
        $name = $request_data['name']; 

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations; 

        $result = $db->createUser($email, $hash_password, $name);
        
        if($result == USER_CREATED){

            $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'User created successfully';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json' , 'charset=utf-8')
                        ->withStatus(201);

        }else if($result == USER_FAILURE){

            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    

        }else if($result == USER_EXISTS){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'User Already Exists';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});

$app->post('/userlogin', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('email', 'password'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $email = $request_data['email'];
        $password = $request_data['password'];
        
        $db = new DbOperations; 

        $result = $db->userLogin($email, $password);

        if($result == USER_AUTHENTICATED){
            
            $user = $db->getUserByEmail($email);
            $response_data = array();

            $response_data['error']=false; 
            $response_data['message'] = 'Login Successful';
            $response_data['user']=$user; 

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    

        }else if($result == USER_NOT_FOUND){
            $response_data = array();

            $response_data['error']=true; 
            $response_data['message'] = 'User not exist';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    

        }else if($result == USER_PASSWORD_DO_NOT_MATCH){
            $response_data = array();

            $response_data['error']=true; 
            $response_data['message'] = 'Invalid credential';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);  
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});

$app->get('/allusers', function(Request $request, Response $response){

    $db = new DbOperations; 

    $users = $db->getAllUsers();

    $response_data = array();

    $response_data['error'] = false; 
    $response_data['users'] = $users; 
    echo "striiiiiiiiiiiiiiiiiiiiiiiiiing   striiiiiiiiiiiiiiiiiiiiiiiiiing";

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  

});


$app->get('/allmessages', function(Request $request, Response $response){

    $db = new DbOperations; 

    $messages = $db->getAllMessages();
    print_r($messages);

    $response_data = array();

    $response_data['error'] = false; 
    $response_data['message'] = $messages; 

    //$object = (object) $messages;
    //$messages_object = $object->message1fdsa;
    //$array = get_object_vars($object);

    $response->write(json_encode($response_data));
    //JSON_FORCE_OBJECT

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
    exit();

});



$app->post('/usermessages', function(Request $request , Response $response){

    if(!haveEmptyParameters(array('username'), $request, $response)){
    
    //parse the request data
    $request_data = $request->getParsedBody(); 

    //fetch the username that i send
    $username = $request_data['username'];

    //create DbOperations object and i can use all of the functions of the class
    $db = new DbOperations;

    //fetch the messages from the database and save them to the variable $messages
    $messages = $db->getUserMessages($username);

    //create array
    $response_data = array();
    
    //set data to array 
    $response_data['error'] = false;
    $response_data['messages'] = $messages;

    $response->write(json_encode($response_data));
    
    }

    return $response
    ->withHeader('Content-type' , 'application/json')
    ->withStatus(200);


} );

$app->put('/updateSincValue', function(Request $request , Response $response){

    if(!haveEmptyParameters(array('username'), $request, $response)){
    
    //parse the request data
    $request_data = $request->getParsedBody(); 

    //fetch the username that i send
    $username = $request_data['username'];

    //create DbOperations object and i can use all of the functions of the class
    $db = new DbOperations;

    //fetch the messages from the database and save them to the variable $messages
    $messages = $db->updateUserSinchronization($username);

    //create array
    $response_data = array();
    
    //set data to array 
    $response_data['error'] = false;
    $response_data['message'] = $messages;

    $response->write(json_encode($response_data));
    
    }

    return $response
    ->withHeader('Content-type' , 'application/json')
    ->withStatus(200);


} );

//update the user 
$app->put('/updateuser/{id}', function(Request $request, Response $response, array $args){



    if(!haveEmptyParameters(array('email','name'), $request, $response)){

        $id = $args['id'];

        $request_data = $request->getParsedBody(); 
        $email = $request_data['email'];
        $name = $request_data['name'];
        
        $db = new DbOperations; 

        

        if($db->updateUser($email, $name, $id)){
            $response_data = array(); 
            $response_data['error'] = false; 
            $response_data['message'] = 'User Updated Successfully';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user; 

            $response->write(json_encode($response_data));

            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);  
        
        }else{
            $response_data = array(); 
            $response_data['error'] = true; 
            $response_data['message'] = 'Please try again later';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user; 

            $response->write(json_encode($response_data));

            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);  
              
        }

    }
    
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  

});

$app->put('/updatepassword', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('currentpassword', 'newpassword', 'email'), $request, $response)){
        
        $request_data = $request->getParsedBody(); 

        $currentpassword = $request_data['currentpassword'];
        $newpassword = $request_data['newpassword'];
        $email = $request_data['email']; 

        $db = new DbOperations; 

        $result = $db->updatePassword($currentpassword, $newpassword, $email);

        if($result == PASSWORD_CHANGED){
            $response_data = array(); 
            $response_data['error'] = false;
            $response_data['message'] = 'Password Changed';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                            ->withStatus(200);

        }else if($result == PASSWORD_DO_NOT_MATCH){
            $response_data = array(); 
            $response_data['error'] = true;
            $response_data['message'] = 'You have given wrong password';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
        }else if($result == PASSWORD_NOT_CHANGED){
            $response_data = array(); 
            $response_data['error'] = true;
            $response_data['message'] = 'Some error occurred';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);  
});

$app->delete('/deleteuser/{id}', function(Request $request, Response $response, array $args){
    $id = $args['id'];

    $db = new DbOperations; 

    $response_data = array();

    if($db->deleteUser($id)){
        $response_data['error'] = false; 
        $response_data['message'] = 'User has been deleted';    
    }else{
        $response_data['error'] = true; 
        $response_data['message'] = 'Plase try again later';
    }

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);
});

function haveEmptyParameters($required_params, $request, $response){
    $error = false; 
    $error_params = '';
    $request_params = $request->getParsedBody(); 

    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
            $error = true; 
            $error_params .= $param . ', ';
        }
    }

    if($error){
        $error_detail = array();
        $error_detail['error'] = true; 
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    if(!$error){
        $error_detail = array();
        $error_detail['error'] = false; 
        $error_detail['message'] = 'No !!!Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        //$response->write(json_encode($error_detail));
    }
    return $error; 
}

$app->run();

