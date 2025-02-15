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

class Connexion
{

    private static $_instance = null;
    private $pdo;

    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @return Singleton de la connexion
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new connexion();
        }
        return self::$_instance;
    }

    /**
     * Fonction permettant d'ouvrir une connexion à la base de données.
     */
    private function __construct()
    {
        try {
            $this->pdo = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::ATTR_PERSISTENT => true
            ));
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * Fonction permettant de fermer la connexion à la base de données.
     */
    public function __destruct()
    {
        $this->pdo = null;
    }
    /**
     * Fonction permettant d'authentifier un utilisateur
     * @param email L'addresse email de l'utilisateur
     * @param password Password de l'utilisateur
     * 
     * @return le nom prénom de l'utilisateur connecté ou une erreur
     */
    public function authenticateUser($email, $password)
    {
        $query = "SELECT pk_user, name, familyName, email, password FROM t_user WHERE email = :email";
        $params = array('email' => $email);
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute($params);
            $user = $queryPrepared->fetch(PDO::FETCH_ASSOC);
            $final = NULL;
            if ($user && password_verify($password, $user['password'])) {
                $final = new User($user["name"], $user["familyName"], $user["email"], $user["pk_user"]);
                $final->setIsAuthenticated(TRUE);
            } else {
                $final = new ErrorAnswer("The username and password do not match.", 401);
            }
            return $final;
        } catch (PDOException $e) {
            return new ErrorAnswer("An error occurred while trying to authenticate the user.", 500);
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
    public function getUserPkPortfolio($pkUser)
    {
        $query = "SELECT pk_portfolio FROM t_portfolio where fk_user = :fkuser";
        $params = array('fkuser' => $pkUser);
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute($params);
            $pk_portfolio = $queryPrepared->fetch(PDO::FETCH_ASSOC);
            if ($pk_portfolio) {
                $pk_portfolio = $pk_portfolio['pk_portfolio'];
            } else {
                $pk_portfolio = $this->createUserPortfolio($pkUser);
                if (!$pk_portfolio) {
                    $pk_portfolio = new ErrorAnswer("An error occurred while trying to create the portfolio.", 500);
                }
            }
            return $pk_portfolio;
        } catch (PDOException $e) {
            return new ErrorAnswer("An error occurred while trying to fetch the user's portfolio.", 500);
        }
    }
    /**
     * Méthode permettant de créer un nouveau portfolio a l'utilisateur dans la DB
     * 
     * @param pkUser l'utilisateur a qui il faut créer le portfolio
     * 
     * @return int la pk du portfolio
     */
    public function createUserPortfolio($pkUser)
    {
        $query = "INSERT INTO t_portfolio (fk_user) VALUES (:fk_user)";
        $params = array('fk_user' => $pkUser);
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute($params);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            return new ErrorAnswer("An error occurred while trying to fetch the user's portfolio.", 500);
        }
    }
    /**
     * Méthode permettant de récuperer les positions d'un utilisateur
     * 
     * @param pkUser l'utilisateur concerné
     * 
     * @return Array Les positions
     */
    public function getUserPositions()
    {
        $positions = NULL;
        if (isset($_SESSION['user']) and $_SESSION['user']->isauthenticated()) {
            $user = $_SESSION['user'];
            $pkPortfolio = $this->getUserPkPortfolio($user->getPk());
            $query = "SELECT avgBuyPrice, boughtQuantity, soldQuantity, avgSoldPrice, name FROM tr_portfolio_stock INNER JOIN t_stock ON fk_stock = pk_stock WHERE fk_portfolio = :fkPortfolio";
            $params = array('fkPortfolio' => $pkPortfolio);
            try {
                $queryPrepared = $this->pdo->prepare($query);
                $queryPrepared->execute($params);
                $positions = $queryPrepared->fetchAll(PDO::FETCH_ASSOC);
                return $positions;
            } catch (PDOException $e) {
                return new ErrorAnswer("An error occurred while trying to fetch the user's positions.", 500);
            }
        }
    }
    /**
     * Méthode permettant de récuperer une position spécifique de l'utilisateur
     * 
     * @param stockName le nom de la position a récuperer
     * 
     * @return Array la position
     */
    public function getSpecificUserPosition($stockName)
    {
        if (isset($_SESSION['user']) and $_SESSION['user']->isauthenticated()) {
            $user = $_SESSION['user'];
            $query = "SELECT avgBuyPrice, boughtQuantity, soldQuantity, avgSoldPrice, name FROM tr_portfolio_stock INNER JOIN t_stock ON fk_stock = :pk_stock WHERE name = :asset and fk_portfolio = :fk_portfolio";
            $params = array('pk_stock' => $this->verifyAsset($stockName), 'asset' => $stockName, 'fk_portfolio' => $user->getPkPortfolio());
            try {
                $queryPrepared = $this->pdo->prepare($query);
                $queryPrepared->execute($params);
                $position = $queryPrepared->fetch(PDO::FETCH_ASSOC);
                return $position;
            } catch (PDOException $e) {
                return new ErrorAnswer("An error occurred while trying to fetch the user's " . $stockName . " position.", 500);
            }
        }
    }
    /**
     * Méthode permettant de vérifier si l'action est déja enregistrée dans la DB et si ce n'est pas le cas, on va la créer
     * 
     * @param ticker le symbol représentant l'entreprise
     * 
     * @return int la pk du stock ou -1 si celui-ci est invalide
     */
    public function verifyAsset($ticker)
    {
        //Vérifier si le ticker existe belle et bien
        $apiKey = "cudscnhr01qiosq11fb0cudscnhr01qiosq11fbg";
        $url = "https://finnhub.io/api/v1/stock/profile2?symbol=" . urlencode($ticker) . "&token=" . $apiKey;
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        if (isset($data["ticker"])) {
            //Vérifier si le stock est déja dans la DB
            $query = "select pk_stock from BaoBull.t_stock where name = :ticker";
            $params = array('ticker' => $data["ticker"]);
            try {
                $queryPrepared = $this->pdo->prepare($query);
                $queryPrepared->execute($params);
                $stock = $queryPrepared->fetch(PDO::FETCH_ASSOC);
                $pkStock = -1;
                if ($stock) {
                    $pkStock = $stock['pk_stock'];
                } else {
                    $query = "INSERT INTO BaoBull.t_stock (name) VALUES (:ticker)";
                    $params = $params = array('ticker' => $data["ticker"]);
                    $queryPrepared = $this->pdo->prepare($query);
                    $queryPrepared->execute($params);
                    $pkStock = $this->pdo->lastInsertId();
                }
                return $pkStock;
            } catch (PDOException $e) {
                return new ErrorAnswer("Error while trying to verify the ticker '" . $ticker . "'.", 500);
            }
        }
    }

    /**
     * Méthode permettant d'ajouter un stock dans un portfolio.
     * 
     * @param avgBuyPrice le prix d'achat moyen
     * @param boughtQuantity la quantité de stock
     * @param stockName le nom du stock acheté
     * 
     * @return les positions de l'utilisateur mise à jours ou une erreur
     */
    public function addPosition($avgBuyPrice, $boughtQuantity, $stockName)
    {
        if (isset($_SESSION['user']) and $_SESSION['user']->isauthenticated()) {
            $user = $_SESSION['user'];
            $existingPosition = $this->getSpecificUserPosition($stockName);
            //S'il y a eu une erreur lors du fetching des positions
            if ($existingPosition instanceof ErrorAnswer) {
                return $existingPosition;
            }
            $fkStock = $this->verifyAsset($stockName);
            $query = "";
            $params = "";
            //Vérifier si on a déja une position afin de faire qu'une entrée par stock
            if ($existingPosition) {
                $totalAmount = $existingPosition['boughtQuantity'] + $boughtQuantity;
                $avgPrice = ($boughtQuantity * $avgBuyPrice + $existingPosition['boughtQuantity'] * $existingPosition['avgBuyPrice']) / ($boughtQuantity + $existingPosition['boughtQuantity']);
                $query = "UPDATE tr_portfolio_stock SET avgBuyPrice = :avgBuyPrice, boughtQuantity=:boughtQuantity WHERE fk_portfolio=:fkPortfolio and fk_stock=:fk_stock";
                $params = array('avgBuyPrice' => $avgPrice, 'boughtQuantity' => $totalAmount, 'fkPortfolio' => $user->getPkPortfolio(), 'fk_stock' => $fkStock);
            } else {
                //Si on à pas encore de position, on va premièrement checker si le ticker est déja présent ou non pour après créer la position
                if ($fkStock) {
                    $query = "INSERT INTO BaoBull.tr_portfolio_stock (fk_portfolio, fk_stock, avgBuyPrice, boughtQuantity) VALUES (:fkPortfolio,:fkStock,:avgPrice, :boughtQuantity)";
                    $params = array('fkPortfolio' => $user->getPkPortfolio(), 'fkStock' => $fkStock, 'avgPrice' => $avgBuyPrice, 'boughtQuantity' => $boughtQuantity);
                } else {
                    return new ErrorAnswer("The provided ticker does not exist.", 404);
                }
            }
            try {
                $queryPrepared = $this->pdo->prepare($query);
                $queryPrepared->execute($params);
                return ($this->getUserPositions());
            } catch (PDOException $e) {
                return new ErrorAnswer("Error while trying to create a position for the stock '" . $stockName . "'.", 500);
            }
        } else {
            return new ErrorAnswer("The requested action requires you to be logged in.", 401);
        }
    }
}
