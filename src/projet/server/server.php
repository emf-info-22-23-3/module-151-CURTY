<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
include_once('workers/db/connexion.php');
include_once('beans/ErrorAnswer.php');
if (isset($_SERVER['REQUEST_METHOD'])) {
    $connexion = Connexion::getInstance();
    $missingParamError = new ErrorAnswer("Can not perform the requested action due to missing parameters.", 400);
    $userUnauthorized = new ErrorAnswer("The requested action requires you to be authenticated.", 401);
    session_start();
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_SESSION['user']) and $_SESSION['user']->isauthenticated()) {
                if (isset($_GET['action'])) {
                    if (isset($_GET['action']) and $_GET['action'] == 'getPositions') {
                        $positions = $connexion->getUserPositions();
                        if ($positions == NULL) {
                            http_response_code(401);
                            echo json_encode("{error: The user is not authorized to access these positions}");
                        } else {
                            http_response_code(200);
                            echo json_encode($positions);
                        }
                    } else if (isset($_GET['action']) and $_GET['action'] == 'test') {
                        http_response_code(200);
                        echo json_encode($connexion->addPosition(150, 1, "SOUN"));
                    }
                } else {
                    http_response_code($missingParamError->getStatus());
                    echo json_encode($missingParamError);
                }
            } else {
                http_response_code($userUnauthorized->getStatus());
                echo json_encode($userUnauthorized);
            }
            break;
        case 'POST':
            $json = file_get_contents('php://input');
            $data = json_decode($json, TRUE);
            //Continue to implement the logged in verification here and remove it in the dbWorker
            if (isset($_SESSION['user']) and $_SESSION['user']->isauthenticated()) {
            }

            if (isset($data['action'])) {
                if ($data['action'] == "login") {
                    if (isset($data['email']) and isset($data['password'])) {
                        $user = $connexion->authenticateUser($data['email'], $data['password']);
                        if ($user instanceof ErrorAnswer) {
                            http_response_code($user->getStatus());
                            echo json_encode($user);
                        } else {
                            $portfolioId = $connexion->getUserPkPortfolio($user->getPk());
                            $user->setFkPortfolio($portfolioId);
                            $_SESSION['user'] = $user;
                            http_response_code(200);
                            echo json_encode($user);
                        }
                    } else {
                        http_response_code($missingParamError->getStatus());
                        echo json_encode($missingParamError);
                    }
                } else if ($data['action'] == "disconnect") {
                    unset($_SESSION['user']);
                    session_destroy();
                } else if ($data['action'] == "addStock") {
                    if (isset($data['avgBuyPrice']) and isset($data['boughtQuantity']) and isset($data['asset'])) {
                        if (is_numeric($data['avgBuyPrice']) and $data['avgBuyPrice'] > 0 and is_numeric($data['boughtQuantity']) and $data['boughtQuantity'] > 0) {
                            $return = $connexion->addPosition($data['avgBuyPrice'], $data['boughtQuantity'], $data['asset']);
                            if ($return instanceof ErrorAnswer) {
                                http_response_code($return->getStatus());
                                echo json_encode($return);
                            } else {
                                http_response_code(200);
                                echo json_encode($return);
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
}
