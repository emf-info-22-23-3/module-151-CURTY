<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
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
        $json = file_get_contents('php://input');
        $data = json_decode($json, TRUE);
            if(isset($data['action']) and $data['action'] == "login"){
                if (isset($data['email']) and isset($data['password']))
                {
                    $user = $connexion->authenticateUser($data['email'],$data['password']);
                    if($user == NULL){
                        http_response_code(401);
                        echo json_encode("{error: User not found or password incorrect}");
                    }else{
                        $user->getUserPortfolio($user->pk_user);
                        $_SESSION['user'] = $user;
                        http_response_code(200);
                        echo json_encode($user);
                    }
                }
                else{
                    http_response_code(400);
                    echo 'Error, the request is missing some parameters';
                }
            }else if(isset($data['action']) and $data['action'] == "disconnect"){
                unset($_SESSION['user']);
                session_destroy();
            }else if(isset($data['action']) and $data['action'] == "addStock"){

            }
            else{
                echo "Action not set";
            }
            break;
        case 'PUT':
            break;
        case 'DELETE':
            break;
    }
}
?>
