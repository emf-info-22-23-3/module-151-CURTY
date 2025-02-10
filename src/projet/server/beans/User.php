<?php
/**
 * Classe représentant un utilisateur
 */
class User
{
    public $name;
    public $familyName;
    public $email;
    private $pk_user;
    private $authenticated;

    /**
     * Constructeur de la classe User.
     *
     * @param string $name Le prénom de l'utilisateur.
     * @param string $familyName Le nom de famille de l'utilisateur.
     * @param string $email L'adresse email de l'utilisateur.
     */
    public function __construct($name, $familyName, $email,$pk_user)
    {
        $this->name = $name;
        $this->familyName = $familyName;
        $this->email = $email;
        $this->pk_user= $pk_user;
        $this->authenticated = FALSE;
    }
    /**
     * Getter pour la pk_user
     * 
     * @return la pk de l'utilisateur
     */
    public function getPk(){
        return $this->pk_user;
    }
    /**
     * Getter pour le flag authenticated
     * 
     * @return si l'utilisateur est authentifié
     */
    public function isauthenticated(){
        return $this->authenticated;
    }
    /**
     * Setter pour le flag authenticated
     * 
     * @param si l'utilisateur est authentifiée ou non
     */
    public function setIsAuthenticated($authenticated){
        $this->authenticated = $authenticated;
    }
}
?>