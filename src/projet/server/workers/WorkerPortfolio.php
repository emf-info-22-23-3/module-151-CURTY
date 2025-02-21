<?php
class WorkerPortfolio
{
    private $db;

    /**
     * Fonction qui va initialiser tous les attributs de la classe
     */
    public function __construct()
    {
        $this->db = WorkerDb::getInstance();
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
        $pkPortfolio = $this->db->selectQuerySingleResult($query, $params);
        if ($pkPortfolio and !($pkPortfolio instanceof ErrorAnswer)) {
            $pkPortfolio = $pkPortfolio['pk_portfolio'];
        } else {
            $pkPortfolio = $this->createUserPortfolio($pkUser);
        }
        return $pkPortfolio;
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
        $toReturn = $this->db->executeQuery($query, $params);
        if (!($toReturn instanceof ErrorAnswer) and $toReturn == 1) {
            $toReturn = $this->db->getLastId("t_portfolio");
        }
        return $toReturn;
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
        $user = $_SESSION['user'];
        $pkPortfolio = $user->getPkPortfolio();
        if ($pkPortfolio == -1) {
            $positions = new ErrorAnswer("The server was not able to retreive you portfolio. Please try to log back in in a moment", 500);
        } else {
            $query = "SELECT avgBuyPrice, boughtQuantity, soldQuantity, avgSoldPrice, name FROM tr_portfolio_stock INNER JOIN t_stock ON fk_stock = pk_stock WHERE fk_portfolio = :fkPortfolio";
            $params = array('fkPortfolio' => $pkPortfolio);
            $positions = $this->db->selectQuery($query, $params);
        }
        return $positions;
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
     * Méthode permettant de vérifier si l'action est déja enregistrée dans la DB et si ce n'est pas le cas, on va la créer à condition que le ticker existe
     * PS: Méthode 100% fonctionel sans time out depuis la maison
     * @param ticker le symbol représentant l'action
     * 
     * @return int la pk du stock ou une ErrorAnswer en cas de ticker invalide ou d'erreur avev la DB
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
            http_response_code(500);
            return new ErrorAnswer("Error while trying to fetch the ticker from finnhub. Ex:" . $e, 500);
        }
    }

    /**
     * Méthode permettant d'ajouter un stock dans un portfolio.
     * 
     * @param avgBuyPrice le prix d'achat moyen
     * @param boughtQuantity la quantité de stock
     * @param stockName le nom du stock acheté
     * 
     * @return Array les positions de l'utilisateur mise à jours ou une erreur
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
