<?php
class PortfolioCtrl
{
    private $workerPortfolio;

    public function __construct()
    {
        $this->workerPortfolio = new WorkerPortfolio();
    }

    /**
     * Méthode permettant de récuperer les positions d'un utilisateur
     */
    public function getUserPositions()
    {
        return $this->workerPortfolio->getUserPositions();
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
        $toReturn = NULL;
        if ($this->isValidNumber($avgBuyPrice) and $this->isValidNumber($boughtQuantity) and $this->isValidString($stockName)) {
            $toReturn = $this->workerPortfolio->addPosition($avgBuyPrice, $boughtQuantity, $stockName);
        } else {
            $toReturn = BAD_REQUEST;
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
        $toReturn = NULL;
        if ($this->isValidNumber($avgSellPrice) and $this->isValidNumber($soldQuantity) and $this->isValidString($stockName)) {
            $toReturn = $this->workerPortfolio->sellStock($avgSellPrice, $soldQuantity, $stockName);
        } else {
            $toReturn = BAD_REQUEST;
        }
        return $toReturn;
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
        return $this->workerPortfolio->getUserPkPortfolio($pkUser);
    }
    /**
     * Méthode permettant de vérifier qu'un champ soit bien un nombre positif
     *
     * @param $field le champ a vérifier
     * @return boolean si le champ est un numéro valide ou non
     */
    private function isValidNumber($field)
    {
        $isValid = false;
        if (!empty($field) and is_numeric($field) and $field > 0) {
            $isValid = true;
        }
        return $isValid;
    }
    /**
     * Méthode permettant de vérifier qu'un champ est belle est bien un string et qu'il ne soit pas vide.
     * 
     * @param $str Le champ a vérifier
     * @return boolean si le champ est un string valide ou non
     */
    private function isValidString($str)
    {
        $isValid = false;
        if (!empty(trim($str)) and is_string($str)) {
            $isValid = true;
        }
        return $isValid;
    }
}
