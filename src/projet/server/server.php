<?php

/**
 * Script server.php
 * 
 * Script qui sert de endpoint pour les clients. Il va s'occuper de vérifer les paramètres reçus, 
 * retransmettre la requête au bon worker et finalement renvoyer la réponse au client sous forme de JSON 
 * @version 1.0
 * @author Curty Esteban
 * @project BaoBull
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
include_once('beans/ErrorAnswer.php');
include_once('beans/httpReturns.php');
include_once('workers/db/Worker.php');
include_once('beans/User.php');
include_once('workers/WorkerAuthentication.php');
include_once('workers/WorkerPortfolio.php');
if (isset($_SERVER['REQUEST_METHOD'])) {
    session_start();
    $json = file_get_contents('php://input');
    $receivedParams = json_decode($json, TRUE);
    //Vérifier que l'utilisateur soit authitifiée avant de le laisser faire quelque chose d'autre que se logguer
    if (isset($_SESSION['user']) and $_SESSION['user']->isauthenticated()) {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if (isset($_GET['action'])) {
                    if ($_GET['action'] == 'getPositions') {
                        $positions = WorkerPortfolio::getInstance()->getUserPositions();
                        if ($positions instanceof ErrorAnswer) {
                            http_response_code($positions->getStatus());
                            echo json_encode($positions);
                        } else {
                            http_response_code(HTTP_SUCCESS);
                            echo json_encode($positions);
                        }
                    } else {
                    }
                } else {
                    http_response_code(BAD_REQUEST->getStatus());
                    echo json_encode(BAD_REQUEST);
                }
                break;
            case 'POST':
                $json = file_get_contents('php://input');
                $receivedParams = json_decode($json, TRUE);
                if (isset($receivedParams['action'])) {
                    if ($receivedParams['action'] == "login") {
                        http_response_code(HTTP_SUCCESS);
                        echo json_encode($_SESSION['user']);
                    } else if ($receivedParams['action'] == "disconnect") {
                        unset($_SESSION['user']);
                        session_destroy();
                    } else if ($receivedParams['action'] == "addStock") {
                        if (isset($receivedParams['avgBuyPrice']) and isset($receivedParams['boughtQuantity']) and isset($receivedParams['asset'])) {
                            if (is_numeric($receivedParams['avgBuyPrice']) and $receivedParams['avgBuyPrice'] > 0 and is_numeric($receivedParams['boughtQuantity']) and $receivedParams['boughtQuantity'] > 0) {
                                $newPositions = WorkerPortfolio::getInstance()->addPosition($receivedParams['avgBuyPrice'], $receivedParams['boughtQuantity'], $receivedParams['asset']);
                                if ($newPositions instanceof ErrorAnswer) {
                                    http_response_code($newPositions->getStatus());
                                    echo json_encode($newPositions);
                                } else {
                                    http_response_code(HTTP_SUCCESS);
                                    echo json_encode($newPositions);
                                }
                            } else {
                                http_response_code(BAD_REQUEST->getStatus());
                                echo json_encode(BAD_REQUEST);
                            }
                        } else {
                            http_response_code(BAD_REQUEST->getStatus());
                            echo json_encode(BAD_REQUEST);
                        }
                    } else if ($receivedParams['action'] == "sellStock") {
                        if (isset($receivedParams["avgSellPrice"]) and isset($receivedParams["soldQuantity"]) and isset($receivedParams['asset'])) {
                            if (is_numeric($receivedParams["avgSellPrice"]) and $receivedParams["avgSellPrice"] > 0 and is_numeric($receivedParams["soldQuantity"]) and $receivedParams["soldQuantity"] > 0) {
                                $result = WorkerPortfolio::getInstance()->sellStock($receivedParams["avgSellPrice"], $receivedParams["soldQuantity"], $receivedParams['asset']);
                                if ($result instanceof ErrorAnswer) {
                                    http_response_code($result->getStatus());
                                    echo json_encode($result);
                                } else {
                                    http_response_code(HTTP_SUCCESS);
                                    echo json_encode($result);
                                }
                            } else {
                                http_response_code(BAD_REQUEST->getStatus());
                                echo json_encode(BAD_REQUEST);
                            }
                        } else {
                            http_response_code(BAD_REQUEST->getStatus());
                            echo json_encode(BAD_REQUEST);
                        }
                    }
                } else {
                    http_response_code(BAD_REQUEST->getStatus());
                    echo json_encode(BAD_REQUEST);
                }
                break;
            case 'PUT':
                break;
            case 'DELETE':
                break;
        }
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST' and isset($receivedParams['action']) and $receivedParams['action'] == 'login') { //Un utilisateur authentifié peut uniquement se logguer
        if (isset($receivedParams['email']) and isset($receivedParams['password'])) {
            $user = WorkerAuthentication::getInstance()->authenticateUser($receivedParams['email'], $receivedParams['password']);
            if ($user instanceof ErrorAnswer) {
                http_response_code($user->getStatus());
                echo json_encode($user);
            } else {
                $portfolioPk = WorkerPortfolio::getInstance()->getUserPkPortfolio($user->getPk());
                if ($portfolioPk instanceof ErrorAnswer) {
                    http_response_code($portfolioPk->getStatus());
                    echo json_encode($portfolioPk);
                } else {
                    $user->setFkPortfolio($portfolioPk);
                    $_SESSION['user'] = $user;
                    http_response_code(HTTP_SUCCESS);
                    echo json_encode($user);
                }
            }
        } else {
            http_response_code(BAD_REQUEST->getStatus());
            echo json_encode(BAD_REQUEST);
        }
    } else { //Utilisateur non autorisé
        http_response_code($userUnauthorized->getStatus());
        echo json_encode($userUnauthorized);
    }
}
