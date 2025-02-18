<?php
class WorkerPortfolio
{
    private static $_instance = null;
    private $db;
    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @return Singleton de la classe
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new WorkerPortfolio();
        }
        return self::$_instance;
    }

    /**
     * Fonction qui va initialiser tous les attributs de la classe
     */
    private function __construct()
    {
        $this->db = Connexion::getInstance();
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
            $position = $this->db->selectQuerySingleReturn($query, $params);
        }
        return $position;
    }

    /**
     * Méthode permettant de vérifier si l'action est déja enregistrée dans la DB et si ce n'est pas le cas, on va la créer à condition que le ticker existe
     * 
     * @param ticker le symbol représentant l'action
     * 
     * @return int la pk du stock ou une ErrorAnswer en cas de ticker invalide ou d'erreur avev la DB
     */
    public function verifyAsset($ticker)
    {
        $pkStock = NULL;
        try {
            //Demander a finhub si le ticker existe bel et bien
            // $apiKey = "cudscnhr01qiosq11fb0cudscnhr01qiosq11fbg";
            // //$url = "https://finnhub.io/api/v1/stock/profile2?symbol=" . $ticker . "&token=" . $apiKey;
            // $url = 'https://finnhub.io/api/v1/stock/profile2?symbol=SOUN&token=cudscnhr01qiosq11fb0cudscnhr01qiosq11fbg';
            // $url = 'https://finnhub.io/api/v1/stock/profile2?symbol=SOUN&token=cudscnhr01qiosq11fb0cudscnhr01qiosq11fbg';

            // $ch = curl_init();
            // curl_setopt($ch, CURLOPT_URL, $url);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // Timeout in seconds
            // $response = curl_exec($ch);
            // if (curl_errno($ch)) {
            //     echo 'Error:' . curl_error($ch);
            // } else {
            //     echo $response;
            // }
            // curl_close($ch);
            // $response = file_get_contents($url);
            // $data = json_decode($response, true);
            //Vérifier que le stock existe bel et bien
            //if (isset($data["ticker"])) {
            //Vérifier si le stock est déja dans la DB
            $query = "select pk_stock from BaoBull.t_stock where name = :ticker";
            $params = array('ticker' => $ticker);
            $pkStock = $this->db->selectQuerySingleResult($query, $params);
            if ($pkStock and !($pkStock instanceof ErrorAnswer)) {
                $pkStock = $pkStock['pk_stock'];
            } else {
                //$query = 'INSERT INTO BaoBull.t_stock (name) VALUES (:ticker)';
                //$params = $params = array('ticker' => $data['ticker']);
                //$affectedRows = $this->db->executeQuery($query, $params);
                //if (!($affectedRows instanceof ErrorAnswer) and $affectedRows == 1) {
                // $pkStock = $this->db->getLastId('t_stock');
                //}
                $pkStock = new ErrorAnswer("The stock does not exist in the database", 404);
            }
            /*} else {
                //Changer le code de l'erreur
                $pkStock =  new ErrorAnswer("Error, the symbol '" . $ticker . "' does not exist.", 500);
            }*/
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
            }else{
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
                    //Si on à pas encore de position, on va premièrement checker si le ticker est déja présent ou non pour après créer la position
                    if ($fkStock) {
                        $query = "INSERT INTO BaoBull.tr_portfolio_stock (fk_portfolio, fk_stock, avgBuyPrice, boughtQuantity) VALUES (:fkPortfolio,:fkStock,:avgPrice, :boughtQuantity)";
                        $params = array('fkPortfolio' => $user->getPkPortfolio(), 'fkStock' => $fkStock, 'avgPrice' => $avgBuyPrice, 'boughtQuantity' => $boughtQuantity);
                        $toReturn = $this->db->executeQuery($query, $params);
                    } else {
                        $toReturn = new ErrorAnswer("The provided ticker does not exist.", 404);
                    }
                }
                if(!($toReturn instanceof ErrorAnswer)){
                    $toReturn = $this->getUserPositions();
                }
            }
        }
        return $toReturn;
    }
}
