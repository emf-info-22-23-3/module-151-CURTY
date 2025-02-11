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

    /**
     * Méthode permettant de récuperer le portfolio assoscié à un utilisateur et s'il n'en a pas on va le lui créer automatiquement
     * 
     * @param pkUser la pk de l'utilisateur a qui il faut trouver le portfolio
     * 
     * @return int la pk du portfolio de l'utilisateur
     * 
     */
    public function getUserPortfolio($pkUser){
        $query = "SELECT pk_portfolio FROM t_portfolio where fk_user = :fkuser";
        $params = array('fkuser' => $pkUser);
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute($params);
            $fk_portfolio = $queryPrepared->fetch(PDO::FETCH_ASSOC);
            if($fk_portfolio){
                $fk_portfolio = $fk_portfolio['pk_portfolio'];
            }else{
                $fk_portfolio = $this->createUserPortfolio($pkUser);
            }
            return $fk_portfolio;
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    /**
     * Méthode permettant de créer un nouveau portfolio a l'utilisateur dans la DB
     * 
     * @param pkUser l'utilisateur a qui il faut créer le portfolio
     * 
     * @return int la pk du portfolio
     */
    public function createUserPortfolio($pkUser){
        $query = "INSERT INTO t_portfolio (fk_user) VALUES (:fk_user)";
        $params = array('fk_user' => $pkUser);
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute($params);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }
    /**
     * Méthode permettant de récuperer les positions d'un utilisateur
     * 
     * @param pkUser l'utilisateur concerné
     * 
     * @return Array Les positions
     */
    public function getUserPositions(){
        $user = $_SESSION['user'];
        if($user->isauthenticated()){
            $pkPortfolio = $this->getUserPortfolio($user->getPk());
            $query = "SELECT avgBuyPrice, boughtQuantity, soldQuantity, avgSoldPrice, name FROM tr_portfolio_stock INNER JOIN t_stock ON fk_stock = pk_stock WHERE fk_portfolio = :fkPortfolio";
            $params = array('fkPortfolio' => $pkPortfolio);
            try {
                $queryPrepared = $this->pdo->prepare($query);
                $queryPrepared->execute($params);
                $positions = $queryPrepared->fetchAll(PDO::FETCH_ASSOC);
                return $positions;
            } catch (PDOException $e) {
                print "Erreur !: " . $e->getMessage() . "<br/>";
                die();
            }
        }
    }

    public function getSpecificUserPosition($stockName){
        $user = $_SESSION['user'];
        if($user->isauthenticated()){
            $query = "SELECT avgBuyPrice, boughtQuantity, soldQuantity, avgSoldPrice, name FROM tr_portfolio_stock INNER JOIN t_stock ON fk_stock = pk_stock WHERE name = :asset and fk_portfolio = :fk_portfolio";
            $params = array('asset' => $stockName, 'fk_portfolio'=>$user->getPkPortfolio());
            try {
                $queryPrepared = $this->pdo->prepare($query);
                $queryPrepared->execute($params);
                $position = $queryPrepared->fetch(PDO::FETCH_ASSOC);
                return $position;
            } catch (PDOException $e) {
                print "Erreur !: " . $e->getMessage() . "<br/>";
                die();
            }
        }
    }

    /**
     * Méthode permettant d'ajouter un stock dans un portfolio. COntinuer a faire cette möthode afin d'ajouter des positions
     */ 
    public function addPosition($avgBuyPrice, $boughtQuantity, $stockName){
        $user = $_SESSION['user'];
        if($user->isauthenticated()){
            $existingPosition = $this->getSpecificUserPosition($stockName);
            //Vérifier si on a déja une position afin de faire qu'une entrée par stock
            if($existingPosition){
                $totalAmount = $existingPosition['boughtQuantity'] + $boughtQuantity;
                $avgPrice = ($boughtQuantity*$avgBuyPrice+$existingPosition['boughtQuantity']*$existingPosition['avgBuyPrice'])/($boughtQuantity+$existingPosition['boughtQuantity']);
                $updateQuery = "UPDATE tr_portfolio_stock SET avgBuyPrice = :avgBuyPrice, boughtQuantity=:boughtQuantity WHERE fk_portfolio=:fkPortfolio";
                $params = array('avgBuyPrice' => $avgPrice, 'boughtQuantity'=>$totalAmount, 'fkPortfolio'=>$user->getPkPortfolio());
            }else{
                $insertQuery = "INSERT INTO BaoBull.tr_portfolio_stock (fk_portfolio, fk_stock, avgBuyPrice, boughtQuantity) VALUES (:fkPortfolio,:fkStock,:avgPrice, :boughtQuantity)";
                $params = array('fkPortfolio' => $avgPrice, 'fkStock'=>$totalAmount, 'avgPrice'=>$user->getPkPortfolio(), 'boughtQuantity'=>);

            }
        }
    }
}
?>
