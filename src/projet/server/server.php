<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
include_once('workers/db/connexion.php');
include_once('beans/ErrorAnswer.php');
if (isset($_SERVER['REQUEST_METHOD'])) {
    //Déclaration des variables de base
    $connexion = Connexion::getInstance();
    $missingParamError = new ErrorAnswer("Can not perform the requested action due to missing parameters.", 400);
    $userUnauthorized = new ErrorAnswer("The requested action requires you to be authenticated.", 401);
    $httpSuccessCode = 200;
    session_start();

    $json = file_get_contents('php://input');
    $receivedParams = json_decode($json, TRUE);
    //Vérifier que l'utilisateur soit authitifiée avant de le laisser faire quelque chose d'autre que se logguer
    if (isset($_SESSION['user']) and $_SESSION['user']->isauthenticated()) {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if (isset($_GET['action'])) {
                    if ($_GET['action'] == 'getPositions') {
                        $positions = $connexion->getUserPositions();
                        if ($positions instanceof ErrorAnswer) {
                            http_response_code($positions->getStatus());
                            echo json_encode($positions);
                        } else {
                            http_response_code($httpSuccessCode);
                            echo json_encode($positions);
                        }
                    }
                } else {
                    http_response_code($missingParamError->getStatus());
                    echo json_encode($missingParamError);
                }
                break;
            case 'POST':
                $json = file_get_contents('php://input');
                $receivedParams = json_decode($json, TRUE);
                if (isset($receivedParams['action'])) {
                    if ($receivedParams['action'] == "disconnect") {
                        unset($_SESSION['user']);
                        session_destroy();
                    } else if ($receivedParams['action'] == "addStock") {
                        if (isset($receivedParams['avgBuyPrice']) and isset($receivedParams['boughtQuantity']) and isset($receivedParams['asset'])) {
                            if (is_numeric($receivedParams['avgBuyPrice']) and $receivedParams['avgBuyPrice'] > 0 and is_numeric($receivedParams['boughtQuantity']) and $receivedParams['boughtQuantity'] > 0) {
                                $newPositions = $connexion->addPosition($receivedParams['avgBuyPrice'], $receivedParams['boughtQuantity'], $receivedParams['asset']);
                                if ($newPositions instanceof ErrorAnswer) {
                                    http_response_code($newPositions->getStatus());
                                    echo json_encode($newPositions);
                                } else {
                                    http_response_code($httpSuccessCode);
                                    echo json_encode($newPositions);
                                }
                            } else {
                                $error = new ErrorAnswer("The buy price or quantity is not a valid number.", 400);
                                http_response_code($error->getStatus());
                                echo json_encode($error);
                            }
                        } else {
                            http_response_code($missingParamError->getStatus());
                            echo json_encode($missingParamError);
                        }
                    }
                } else {
                    http_response_code($missingParamError->getStatus());
                    echo json_encode($missingParamError);
                }
                break;
            case 'PUT':
                break;
            case 'DELETE':
                break;
        }
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST' and isset($receivedParams['action']) and $receivedParams['action'] == 'login') { //Un utilisateur authentifié peut uniquement se logguer
        if (isset($receivedParams['email']) and isset($receivedParams['password'])) {
            $user = $connexion->authenticateUser($receivedParams['email'], $receivedParams['password']);
            if ($user instanceof ErrorAnswer) {
                http_response_code($user->getStatus());
                echo json_encode($user);
            } else {
                $portfolioId = $connexion->getUserPkPortfolio($user->getPk());
                $user->setFkPortfolio($portfolioId);
                $_SESSION['user'] = $user;
                http_response_code($httpSuccessCode);
                echo json_encode($user);
            }
        } else {
            http_response_code($missingParamError->getStatus());
            echo json_encode($missingParamError);
        }
    } else { //Utilisateur non autorisé
        http_response_code($userUnauthorized->getStatus());
        echo json_encode($userUnauthorized);
    }
}
