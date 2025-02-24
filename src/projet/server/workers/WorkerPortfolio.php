<?php

/**
 * Classe WorkerPortfolio
 *
 * Cette classe est responsable de la gestion du portfolio des utilisateurs. Elle inclut des méthodes
 * pour récupérer, créer et mettre à jour les portfolios et positions des utilisateurs, ainsi que pour vérifier
 * et ajouter des actions dans un portfolio.
 * @version 1.0
 * @author Curty Esteban
 * @project BaoBull
 */
class WorkerPortfolio
{
    private $db;

    /**
     * Constructeur qui initialise l'instance de la base de données.
     */
    public function __construct()
    {
        $this->db = WorkerDb::getInstance();
    }

    /**
     * Récupère l'ID du portfolio associé à un utilisateur ou le crée si nécessaire.
     *
     * @param int $pkUser La clé primaire de l'utilisateur.
     * @return int|ErrorAnswer La pk du portfolio de l'utilisateur ou un erreur
     */
    public function getUserPkPortfolio($pkUser)
    {
        $query = "SELECT pk_portfolio FROM t_portfolio where fk_user = :fkuser";
        $params = array('fkuser' => $pkUser);
        $pkPortfolio = $this->db->selectQuerySingleResult($query, $params);
        if ($pkPortfolio and !($pkPortfolio instanceof ErrorAnswer)) {
            $pkPortfolio = $pkPortfolio['pk_portfolio'];
        } else {
            $pkPortfolio = $this->createUserPortfolio($pkUser);
        }
        return $pkPortfolio;
    }

    /**
     * Crée un nouveau portfolio pour un utilisateur dans la base de données.
     *
     * @param int $pkUser La clé primaire de l'utilisateur.
     * @return int|ErrorAnswer La pk du portfolio nouvellement créé ou une erreur.
     */
    public function createUserPortfolio($pkUser)
    {
        $query = "INSERT INTO t_portfolio (fk_user) VALUES (:fk_user)";
        $params = array('fk_user' => $pkUser);
        $toReturn = $this->db->executeQuery($query, $params);
        if (!($toReturn instanceof ErrorAnswer) and $toReturn == 1) {
            $toReturn = $this->db->getLastId("t_portfolio");
        }
        return $toReturn;
    }

    /**
     * Récupère les positions (actions) d'un utilisateur dans son portfolio.
     *
     * @return array|ErrorAnswer La liste des positions (actions) dans le portfolio de l'utilisateur ou une erreur.
     */
    public function getUserPositions()
    {
        $positions = NULL;
        $user = $_SESSION['user'];
        $pkPortfolio = $user->getPkPortfolio();
        if ($pkPortfolio == -1) {
            $positions = new ErrorAnswer("The server was not able to retrieve you portfolio. Please try to log back in in a moment", 500);
        } else {
            $query = "SELECT avgBuyPrice, boughtQuantity, soldQuantity, avgSoldPrice, name FROM tr_portfolio_stock INNER JOIN t_stock ON fk_stock = pk_stock WHERE fk_portfolio = :fkPortfolio";
            $params = array('fkPortfolio' => $pkPortfolio);
            $positions = $this->db->selectQuery($query, $params);
        }
        return $positions;
    }

    /**
     * Récupère une position spécifique (action) de l'utilisateur dans son portfolio.
     *
     * @param string $stockName Le nom de l'action.
     * @return array|ErrorAnswer Les détails de la position pour l'action spécifiée ou une erreur.
     */
    public function getSpecificUserPosition($stockName)
    {
        $user = $_SESSION['user'];
        $fkStock = $this->verifyAsset($stockName);
        if ($fkStock instanceof ErrorAnswer) {
            return $fkStock;
        } else {
            $query = "SELECT avgBuyPrice, boughtQuantity, soldQuantity, avgSoldPrice, name FROM tr_portfolio_stock INNER JOIN t_stock ON fk_stock = :fk_stock WHERE name = :asset and fk_portfolio = :fk_portfolio";
            $params = array('fk_stock' => $fkStock, 'asset' => $stockName, 'fk_portfolio' => $user->getPkPortfolio());
            $position = $this->db->selectQuerySingleResult($query, $params);
        }
        return $position;
    }

    /**
     * Vérifie si l'action existe dans la base de données, et si ce n'est pas le cas, l'ajoute.
     * 
     * @param string $ticker Le symbole de l'action.
     * @return int|ErrorAnswer La clé primaire de l'action, ou une erreur si l'action est invalide ou en cas d'erreur de base de données.
     */
    public function verifyAsset($ticker)
    {
        $pkStock = NULL;
        try {
            //Demander a finhub si le ticker existe bel et bien
            $apiKey = "cudscnhr01qiosq11fb0cudscnhr01qiosq11fbg";
            $url = "https://finnhub.io/api/v1/stock/profile2?symbol=" . urlencode($ticker) . "&token=" . $apiKey;
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            if (isset($data["ticker"])) {
                //Vérifier si le stock est déja dans la db
                $query = "select pk_stock from BaoBull.t_stock where name = :ticker";
                $params = array('ticker' => $data["ticker"]);
                $stock = $this->db->selectQuerySingleResult($query, $params);
                if ($stock and !($stock instanceof ErrorAnswer)) {
                    $pkStock = $stock['pk_stock'];
                } else if (!($stock instanceof ErrorAnswer)) {
                    $query = "INSERT INTO BaoBull.t_stock (name) VALUES (:ticker)";
                    $params = $params = array('ticker' => $data["ticker"]);
                    $affectedRows = $this->db->executeQuery($query, $params);
                    if (!($affectedRows instanceof ErrorAnswer) and $affectedRows == 1) {
                        $pkStock = $this->db->getLastId('t_stock');
                    } else {
                        $pkStock = $affectedRows;
                    }
                } else {
                    $pkStock = $stock;
                }
            }
            return $pkStock;
        } catch (Exception $e) {
            return new ErrorAnswer("Error while trying to fetch the ticker from finnhub. Ex:" . $e, 500);
        }
    }

    /**
     * Ajoute une nouvelle position (action) dans le portfolio d'un utilisateur.
     *
     * @param float $avgBuyPrice Le prix d'achat moyen de l'action.
     * @param int $boughtQuantity La quantité d'actions achetées.
     * @param string $stockName Le nom de l'action achetée.
     * @return array|ErrorAnswer La liste des positions de l'utilisateur mise à jour, ou une erreur en cas de problème.
     */
    public function addPosition($avgBuyPrice, $boughtQuantity, $stockName)
    {
        $user = $_SESSION['user'];
        $existingPosition = $this->getSpecificUserPosition($stockName);
        $toReturn = NULL;
        //S'il y a eu une erreur lors du fetching des positions
        if ($existingPosition instanceof ErrorAnswer) {
            $toReturn = $existingPosition;
        } else {
            $fkStock = $this->verifyAsset($stockName);
            if ($fkStock instanceof ErrorAnswer) {
                $toReturn = $fkStock;
            } else {
                $query = "";
                $params = "";
                //Vérifier si on a déja une position afin de faire qu'une entrée par stock
                if ($existingPosition) {
                    $totalAmount = $existingPosition['boughtQuantity'] + $boughtQuantity;
                    $avgPrice = ($boughtQuantity * $avgBuyPrice + $existingPosition['boughtQuantity'] * $existingPosition['avgBuyPrice']) / ($boughtQuantity + $existingPosition['boughtQuantity']);
                    $query = "UPDATE tr_portfolio_stock SET avgBuyPrice = :avgBuyPrice, boughtQuantity=:boughtQuantity WHERE fk_portfolio=:fkPortfolio and fk_stock=:fk_stock";
                    $params = array('avgBuyPrice' => $avgPrice, 'boughtQuantity' => $totalAmount, 'fkPortfolio' => $user->getPkPortfolio(), 'fk_stock' => $fkStock);
                    $toReturn = $this->db->executeQuery($query, $params);
                } else {
                    if ($fkStock) {
                        $query = "INSERT INTO BaoBull.tr_portfolio_stock (fk_portfolio, fk_stock, avgBuyPrice, boughtQuantity) VALUES (:fkPortfolio,:fkStock,:avgPrice, :boughtQuantity)";
                        $params = array('fkPortfolio' => $user->getPkPortfolio(), 'fkStock' => $fkStock, 'avgPrice' => $avgBuyPrice, 'boughtQuantity' => $boughtQuantity);
                        $toReturn = $this->db->executeQuery($query, $params);
                    } else {
                        $toReturn = new ErrorAnswer("The provided ticker does not exist.", 404);
                    }
                }
                if (!($toReturn instanceof ErrorAnswer)) {
                    $toReturn = $this->getUserPositions();
                }
            }
        }
        return $toReturn;
    }

    /**
     * Réduit la quantité d'une position d'action dans le portfolio de l'utilisateur.
     *
     * @param string $stockName Le nom de l'action.
     * @param int $soldQuantity La quantité d'actions vendues.
     * @param float $avgSellPrice Le prix de vente moyen de l'action.
     * @return array|ErrorAnswer La liste des positions de l'utilisateur mise à jour, ou une erreur en cas de problème.
     */
    public function sellStock($avgSellPrice, $soldQuantity, $stockName)
    {
        $user = $_SESSION['user'];
        $existingPosition = $this->getSpecificUserPosition($stockName);
        $toReturn = NULL;
        //Si on a bien récuperer une position
        if ($existingPosition instanceof ErrorAnswer) {
            $toReturn = $existingPosition;
        } else {
            if ($existingPosition) {
                $boughtQuantity = $existingPosition['boughtQuantity'];
                $alreadySoldQuantity = $existingPosition['soldQuantity'];
                $currentHoldingAmount = $boughtQuantity - $alreadySoldQuantity;
                $avgSoldPrice = $existingPosition['avgSoldPrice'];
                $fkStock = $this->verifyAsset($stockName);
                if ($fkStock instanceof ErrorAnswer) {
                    $toReturn = $fkStock;
                } else if ($fkStock) {
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
                        $affectedRows = $this->db->executeQuery($query, $params);
                        if (!($affectedRows instanceof Error) and $affectedRows == 1) {
                            $toReturn = $this->getUserPositions();
                        } else {
                            $toReturn = $affectedRows;
                        }
                    } else {
                        $toReturn = new ErrorAnswer("Can not sell " . $soldQuantity . " shares of " . $stockName . " because you current holdings are too small.", 422);
                    }
                }
            } else {
                $toReturn = new ErrorAnswer("You do not have any position in " . $stockName, 400);
            }
        }
        return $toReturn;
    }
}
