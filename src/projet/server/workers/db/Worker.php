<?php
include_once('configConnexion.php');
/**
 * Classe connexion
 *
 * Cette classes va gérer les envoies de requêtes a la base de données
 *
 * @version 1.0
 * @author Curty Esteban
 * @project BaoBull
 */

class Connexion
{

    private static $_instance = null;
    private $pdo;
    private $dbError;
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
            $this->dbError = new ErrorAnswer("Error while trying to access the database. Please try again in a moment.", 500);
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
     * Fonction permettant d'exécuter un select dans MySQL.
     * 
     * @param String $query. Requête à exécuter.
     * @param Array $params. Contient les paramètres à ajouter à la requête (null si aucun paramètre n'est requis)
     * @return toutes les lignes du select
     */
    public function selectQuery($query, $params)
    {
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute($params);
            return $queryPrepared->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return $this->dbError;
        }
    }

    /**
     * Fonction permettant d'exécuter un select dans MySQL.
     * 
     * @param String $query. Requête à exécuter.
     * @param Array $params. Contient les paramètres à ajouter à la requête (null si aucun paramètre n'est requis)
     * @return toutes les lignes du select
     */
    public function selectQuerySingleResult($query, $params)
    {
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute($params);
            return $queryPrepared->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return $this->dbError;
        }
    }
    /**
     * Fonction permettant d'exécuter une requête MySQL.
     * A utiliser pour les UPDATE, DELETE, INSERT.
     *
     * @param String $query. Requête à exécuter.
     * @param Array $params. Contient les paramètres à ajouter à la requête  (null si aucun paramètre n'est requis)
     * @return le nombre de lignes affectées
     */
    public function executeQuery($query, $params)
    {
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryRes = $queryPrepared->execute($params);
            return $queryRes;
        } catch (PDOException $e) {
            return $this->dbError;
        }
    }
    /**
     * Fonction permettant d'obtenir le dernier id inséré.
     * 
     * @param String $table. la table où a été inséré l'objet. 
     * @return int: l'id du dernier élément inséré.
     */
    public function getLastId($table)
    {
        try {
            $lastId = $this->pdo->lastInsertId($table);
            return $lastId;
        } catch (PDOException $e) {
            return $this->dbError;
        }
    }
    /**
     * Méthode permettant de réduire la taille d'une position.
     * 
     * @param stockName le nom du stock
     * @param soldQuantity la quantité vendue
     * @param avgSellPrice le prix de vente moyen
     * 
     * @return Array les positions de l'utilisateur mise à jours ou une erreur
     */
    public function sellStock($avgSellPrice, $soldQuantity, $stockName)
    {
        $user = $_SESSION['user'];
        $existingPosition = $existingPosition = $this->getSpecificUserPosition($stockName);
        $toReturn = NULL;
        //Si on a bien récuperer une position
        if (!($existingPosition instanceof ErrorAnswer) and $existingPosition) {
            $boughtQuantity = $existingPosition['boughtQuantity'];
            $alreadySoldQuantity = $existingPosition['soldQuantity'];
            $currentHoldingAmount = $boughtQuantity - $alreadySoldQuantity;
            $avgSoldPrice = $existingPosition['avgSoldPrice'];
            $fkStock = $this->verifyAsset($stockName);
            //Vérifier qu'on ait bien la PK du stock
            if (!($fkStock instanceof ErrorAnswer)) {
                //Vérifier qu'on ait pas déjà tous vendu et que la quantité qu'on veut vendre soit pas trop grande
                if ($currentHoldingAmount > 0 and $soldQuantity <= $currentHoldingAmount) {
                    $query = "update tr_portfolio_stock set soldQuantity=:soldQuantity, avgSoldPrice=:avgSoldPrice where fk_portfolio=:fkPortfolio and fk_stock = :fkStock";
                    $params = "";
                    //Vérifier si on à déjà vendu une fois ou pas
                    if ($alreadySoldQuantity == 0) {
                        $params = array('soldQuantity' => $soldQuantity, 'avgSoldPrice' => $avgSellPrice, 'fkPortfolio' => $user->getPkPortfolio(), 'fkStock' => $fkStock);
                    } else {
                        $totalSoldQuantity = $soldQuantity + $alreadySoldQuantity;
                        $newAvgSoldPrice = ($alreadySoldQuantity * $avgSoldPrice + $soldQuantity * $avgSellPrice) / ($alreadySoldQuantity + $soldQuantity);
                        $params = array('soldQuantity' => $totalSoldQuantity, 'avgSoldPrice' => $newAvgSoldPrice, 'fkPortfolio' => $user->getPkPortfolio(), 'fkStock' => $fkStock);
                    }
                    try {
                        $queryPrepared = $this->pdo->prepare($query);
                        $queryPrepared->execute($params);
                        return ($this->getUserPositions());
                    } catch (PDOException $e) {
                        $toReturn = new ErrorAnswer("Error while trying to sell " . $soldQuantity . " of '" . $stockName . "'.", 500);
                    }
                } else {
                    $toReturn = new ErrorAnswer("Can not sell " . $soldQuantity . " shares of " . $stockName . " because you current holdings are too small.", 422);
                }
            } else {
                $toReturn = $fkStock;
            }
        } else {
            $toReturn = new ErrorAnswer("Can not sell '" . $stockName . "'  because no existing position has been found.", 404);
        }
        return $toReturn;
    }
}
