<?php

/**
 * Classe représentant un utilisateur.
 *
 * Cette classe contient les informations de base d'un utilisateur ainsi que des méthodes pour accéder et modifier ces informations.
 * @version 1.0
 * @author Curty Esteban
 * @project BaoBull
 */
class User
{
    public $name;
    public $familyName;
    public $email;
    private $pk_user;
    private $authenticated;
    private $pkPortfolio;
    /**
     * Constructeur de la classe User.
     *
     * @param string $name Le prénom de l'utilisateur.
     * @param string $familyName Le nom de famille de l'utilisateur.
     * @param string $email L'adresse email de l'utilisateur.
     * @param int $pk_user La clé primaire de l'utilisateur dans la base de données.
     */
    public function __construct($name, $familyName, $email, $pk_user)
    {
        $this->name = $name;
        $this->familyName = $familyName;
        $this->email = $email;
        $this->pk_user = $pk_user;
        $this->authenticated = FALSE;
        $this->pkPortfolio = -1;
    }
    /**
     * Getter pour la clé primaire de l'utilisateur.
     *
     * @return int La clé primaire de l'utilisateur.
     */
    public function getPk()
    {
        return $this->pk_user;
    }
    /**
     * Getter pour le flag d'authentification de l'utilisateur.
     *
     * @return bool True si l'utilisateur est authentifié, sinon False.
     */
    public function isauthenticated()
    {
        return $this->authenticated;
    }
    /**
     * Setter pour le flag d'authentification de l'utilisateur.
     *
     * @param bool $authenticated True si l'utilisateur doit être marqué comme authentifié, sinon False.
     */
    public function setIsAuthenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }
    /**
     * Getter pour la clé primaire du portfolio de l'utilisateur.
     *
     * @return int La clé primaire du portfolio de l'utilisateur.
     */
    public function getPkPortfolio()
    {
        return $this->pkPortfolio;
    }
    /**
     * Setter pour la clé primaire du portfolio de l'utilisateur.
     *
     * @param int $pkPortfolio La clé primaire du portfolio.
     */
    public function setFkPortfolio($pkPortfolio)
    {
        $this->pkPortfolio = $pkPortfolio;
    }
}
