<?php
include_once('workers/db/connexion.php');
if (isset($_SERVER['REQUEST_METHOD']))
{
    $connexion = Connexion::getInstance();
    session_start();
    switch ($_SERVER['REQUEST_METHOD'])
    {
        case 'GET':
            break;
        case 'POST':
            if(isset($_POST['action']) and $_POST['action'] == "login"){
                if (isset($_POST['email']) and isset($_POST['password']))
                {
                    $user = $connexion->authenticateUser($_POST['email'],$_POST['password']);
                    if($user == NULL){
                        http_response_code(401);
                        echo json_encode("{error: User not found or password incorrect}");
                    }else{
                        http_response_code(200);
                        echo json_encode($user);
                        $_SESSION['user'] = $user;
                    }
                }
                else{
                    http_response_code(400);
                    echo 'Error, the request is missing some parameters';
                }
            }
            break;
        case 'PUT':
            break;
        case 'DELETE':
            break;
    }
}
?>
