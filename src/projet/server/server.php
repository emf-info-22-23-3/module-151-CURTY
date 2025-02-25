<?php
/**
 * Script server.php
 * 
 * Ce script sert de point d'entrée pour les clients. Il gère les requêtes HTTP en fonction de la méthode utilisée, 
 * vérifie les paramètres reçus, interagit avec les contrôleurs pour traiter les actions demandées 
 * (authentification, enregistrement, gestion du portfolio), et renvoie des réponses au format JSON.
 * 
 * @version 1.0
 * @author Curty Esteban
 * @project BaoBull
 * 
 * @uses ErrorAnswer
 * @uses httpReturns
 * @uses WorkerDb
 * @uses User
 * @uses WorkerAuthentication
 * @uses WorkerPortfolio
 * @uses PortfolioCtrl
 * @uses UserCtrl
 */
include_once('beans/ErrorAnswer.php');
include_once('beans/HttpReturns.php');
include_once('workers/db/WorkerDb.php');
include_once('beans/User.php');
include_once('workers/WorkerAuthentication.php');
include_once('workers/WorkerPortfolio.php');
include_once('ctrl/PortfolioCtrl.php');
include_once('ctrl/UserCtrl.php');
if (isset($_SERVER['REQUEST_METHOD'])) {
    session_start();
    $json = file_get_contents('php://input');
    $receivedParams = json_decode($json, TRUE);
    $portfolioCtrl = new PortfolioCtrl();
    $userCtrl = new UserCtrl();
    if ($_SERVER['REQUEST_METHOD'] == 'POST' and $userCtrl->areParamsSet(array('action'), $receivedParams) and $receivedParams['action'] == 'login') { //Un utilisateur authentifié peut uniquement se logguer
        if ($userCtrl->areParamsSet(array('email', 'password'), $receivedParams)) {
            $user = $userCtrl->authenticateUser($receivedParams['email'], $receivedParams['password']);
            if ($user instanceof ErrorAnswer) {
                session_destroy();
                http_response_code($user->getStatus());
                echo json_encode($user);
            } else {
                $portfolioPk = $portfolioCtrl->getUserPkPortfolio($user->getPk());
                if ($portfolioPk instanceof ErrorAnswer) {
                    session_destroy();
                    http_response_code($portfolioPk->getStatus());
                    echo json_encode($portfolioPk);
                } else {
                    $user->setFkPortfolio($portfolioPk);
                    $_SESSION['user'] = $user;

                    http_response_code(HttpReturns::HttpSuccess());
                    echo json_encode($user);
                }
            }
        } else {
            http_response_code(HttpReturns::BAD_REQUEST()->getStatus());
            echo json_encode(HttpReturns::BAD_REQUEST());
        }
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST' and $userCtrl->areParamsSet(array('action'), $receivedParams) and $receivedParams['action'] == 'register') {
        if ($userCtrl->areParamsSet(array('name', 'familyName', 'email', 'password'), $receivedParams)) {
            $user = $userCtrl->registerUser($receivedParams['name'], $receivedParams['familyName'], $receivedParams['email'], $receivedParams['password']);
            if ($user instanceof ErrorAnswer) {
                session_destroy();
                http_response_code($user->getStatus());
                echo json_encode($user);
            } else {
                $portfolioPk = $portfolioCtrl->getUserPkPortfolio($user->getPk());
                if ($portfolioPk instanceof ErrorAnswer) {
                    session_destroy();
                    http_response_code($portfolioPk->getStatus());
                    echo json_encode($portfolioPk);
                } else {
                    $user->setFkPortfolio($portfolioPk);
                    $_SESSION['user'] = $user;
                    http_response_code(HttpReturns::HttpSuccess());
                    echo json_encode($user);
                }
            }
        }
    } else if ($_SERVER['REQUEST_METHOD'] == 'GET' and $userCtrl->areParamsSet(array('action'), $_GET) and $_GET['action'] == 'userState') {
        http_response_code(200);
        if (isset($_SESSION['user'])) {
            echo json_encode($_SESSION['user']->isauthenticated());
        } else {
            echo json_encode(false);
        }
    } else  if (isset($_SESSION['user']) and $_SESSION['user']->isauthenticated()) {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if ($userCtrl->areParamsSet(array('action'), $_GET)) {
                    if ($_GET['action'] == 'getPositions') {
                        $positions = $portfolioCtrl->getUserPositions();
                        if ($positions instanceof ErrorAnswer) {
                            http_response_code($positions->getStatus());
                            echo json_encode($positions);
                        } else {
                            http_response_code(HttpReturns::HttpSuccess());
                            echo json_encode($positions);
                        }
                    }
                } else {
                    http_response_code(HttpReturns::BAD_REQUEST()->getStatus());
                    echo json_encode(HttpReturns::BAD_REQUEST());
                }
                break;
            case 'POST':
                if ($userCtrl->areParamsSet(array('action'), $receivedParams)) {
                    if ($receivedParams['action'] == "disconnect") {
                        unset($_SESSION['user']);
                        session_destroy();
                        http_response_code(HttpReturns::HttpSuccess());
                    } else if ($receivedParams['action'] == "addStock") {
                        if ($userCtrl->areParamsSet(array('avgBuyPrice', 'boughtQuantity', 'asset'), $receivedParams)) {
                            $newPositions = $portfolioCtrl->addPosition($receivedParams['avgBuyPrice'], $receivedParams['boughtQuantity'], $receivedParams['asset']);
                            if ($newPositions instanceof ErrorAnswer) {
                                http_response_code($newPositions->getStatus());
                                echo json_encode($newPositions);
                            } else {
                                http_response_code(HttpReturns::HttpSuccess());
                                echo json_encode($newPositions);
                            }
                        } else {
                            http_response_code(HttpReturns::BAD_REQUEST()->getStatus());
                            echo json_encode(HttpReturns::BAD_REQUEST());
                        }
                    } else if ($receivedParams['action'] == "sellStock") {
                        if ($userCtrl->areParamsSet(array('avgSellPrice', 'soldQuantity', 'asset'), $receivedParams)) {
                            $result = $portfolioCtrl->sellStock($receivedParams["avgSellPrice"], $receivedParams["soldQuantity"], $receivedParams['asset']);
                            if ($result instanceof ErrorAnswer) {
                                http_response_code($result->getStatus());
                                echo json_encode($result);
                            } else {
                                http_response_code(HttpReturns::HttpSuccess());
                                echo json_encode($result);
                            }
                        } else {
                            http_response_code(HttpReturns::BAD_REQUEST()->getStatus());
                            echo json_encode(HttpReturns::BAD_REQUEST());
                        }
                    }
                } else {
                    http_response_code(HttpReturns::BAD_REQUEST()->getStatus());
                    echo json_encode(HttpReturns::BAD_REQUEST());
                }
                break;
            case 'PUT':
                break;
            case 'DELETE':
                break;
        }
    } else { //Utilisateur non autorisé
        http_response_code(HttpReturns::UNAUTHORIZED()->getStatus());
        echo json_encode(HttpReturns::UNAUTHORIZED());
    }
}
