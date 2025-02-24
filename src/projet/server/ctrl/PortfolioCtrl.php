<?php

/**
 * Classe PortfolioCtrl
 *
 * Cette classe permet de gérer les actions liées au portfolio d'un utilisateur, telles que l'ajout et la vente de positions.
 * @version 1.0
 * @author Curty Esteban
 * @project BaoBull
 */
class PortfolioCtrl
{
    private $workerPortfolio;
    /**
     * Constructeur de la classe.
     * Initialise l'objet WorkerPortfolio pour gérer les actions sur les portfolios.
     */
    public function __construct()
    {
        $this->workerPortfolio = new WorkerPortfolio();
    }

    /**
     * Méthode permettant de récupérer les positions d'un utilisateur.
     *
     * @return Array|ErrorAnswer Les positions de l'utilisateur
     */
    public function getUserPositions()
    {
        return $this->workerPortfolio->getUserPositions();
    }

    /**
     * Méthode permettant d'ajouter une position dans le portfolio de l'utilisateur.
     *
     * @param float $avgBuyPrice Prix d'achat moyen
     * @param float $boughtQuantity Quantité de stocks achetés
     * @param string $stockName Nom du stock
     * 
     * @return array|ErrorAnswer Les positions de l'utilisateur mises à jour ou une erreur
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
     * Méthode permettant de réduire la taille d'une position dans le portfolio.
     *
     * @param float $avgSellPrice Prix de vente moyen
     * @param float $soldQuantity Quantité de stocks vendus
     * @param string $stockName Nom du stock vendu
     * 
     * @return array|ErrorAnswer Les positions de l'utilisateur mises à jour ou une erreur
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
     * Méthode permettant de récupérer la pk du portfolio associé à un utilisateur.
     *
     * @param int $pkUser La clé primaire de l'utilisateur
     * 
     * @return int|ErrorAnswer La clé primaire du portfolio de l'utilisateur ou une erreur
     */
    public function getUserPkPortfolio($pkUser)
    {
        return $this->workerPortfolio->getUserPkPortfolio($pkUser);
    }

    /**
     * Méthode permettant de vérifier qu'un champ soit bien un nombre positif.
     *
     * @param mixed $field Le champ à vérifier
     * 
     * @return bool Si le champ est un nombre valide ou non
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
     * Méthode permettant de vérifier qu'un champ est un string valide (non vide).
     *
     * @param string $str Le champ à vérifier
     * 
     * @return bool Si le champ est un string valide ou non
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
