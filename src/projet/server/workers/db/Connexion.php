<?php

include_once('configConnexion.php');
include_once('beans/User.php');
/**
 * Classe connexion
 *
 * Cette classe de gérer l'accès à la base de données.
 *
 * @version 1.0
 * @author Curty Esteban
 * @project BaoBull
 */

class Connexion {

    private static $_instance = null;
    private $pdo;

    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @return Singleton de la connexion
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new connexion();
        }
        return self::$_instance;
    }

    /**
     * Fonction permettant d'ouvrir une connexion à la base de données.
     */
    private function __construct() {
        try {
            $this->pdo = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::ATTR_PERSISTENT => true));
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * Fonction permettant de fermer la connexion à la base de données.
     */
    public function __destruct() {
        $this->pdo = null;
    }
    /**
     * Fonction permettant d'authentifier un utilisateur
     * @param email L'addresse email de l'utilisateur
     * @param password Password de l'utilisateur
     * 
     * @return le nom prénom de l'utilisateur connecté ou une erreur
     */
    public function authenticateUser($email, $password){
        $query = "SELECT pk_user, name, familyName, email, password FROM t_user WHERE email = :email";
        $params = array('email' => $email);
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute($params);
            $user = $queryPrepared->fetch(PDO::FETCH_ASSOC);
            $final = NULL;
            if($user && password_verify($password, $user['password'])){
                $final = new User($user["name"],$user["familyName"],$user["email"], $user["pk_user"]);
                $final->setIsAuthenticated(TRUE);
            }
            return $final;
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    public function createUser(){

    }
}
?>
