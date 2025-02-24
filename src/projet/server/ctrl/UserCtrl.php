<?php

/**
 * Classe UserCtrl
 *
 * Cette classe est responsable de la gestion des utilisateurs, y compris l'authentification et l'enregistrement.
 * Elle communique avec la classe WorkerAuthentication pour effectuer ces actions et vérifier la validité des données d'entrée.
 *
 * @version 1.0
 * @author Curty Esteban
 * @project BaoBull
 */
class UserCtrl
{
    private $workerAuthentication;
    /**
     * Constructeur de la classe UserCtrl
     * 
     * Initialise un objet WorkerAuthentication pour effectuer l'authentification et l'enregistrement des utilisateurs.
     */
    public function __construct()
    {
        $this->workerAuthentication = new WorkerAuthentication();
    }
    /**
     * Méthode permettant d'authentifier un utilisateur.
     * 
     * @param string $email L'email de l'utilisateur
     * @param string $password Le mot de passe de l'utilisateur
     * 
     * @return User|ErrorAnswer Un objet User si l'authentification réussit, sinon une erreur.
     */
    public function authenticateUser($email, $password)
    {
        $toReturn = NULL;
        if ($this->isValidEmail($email) and $this->isValidString($password)) {
            $toReturn = $this->workerAuthentication->authenticateUser($email, $password);
        } else {
            $toReturn = HttpReturns::BAD_REQUEST();
        }
        return $toReturn;
    }
    /**
     * Méthode permettant d'enregistrer un nouvel utilisateur.
     * 
     * @param string $name Le prénom de l'utilisateur
     * @param string $familyName Le nom de famille de l'utilisateur
     * @param string $email L'email de l'utilisateur
     * @param string $password Le mot de passe de l'utilisateur
     * 
     * @return User|ErrorAnswer Un objet User si l'enregistrement réussit, sinon une erreur.
     */
    public function registerUser($name, $familyName, $email, $password)
    {
        $toReturn = NULL;
        if($this->isValidString($name) and $this->isValidString($familyName) and $this->isValidEmail($email) and $this->isValidPassword($password)) {
            $toReturn = $this->workerAuthentication->register($name, $familyName, $email, $password);
        }else{
            $toReturn = HttpReturns::BAD_REQUEST();
        }
        return $toReturn;
    }

    /**
     * Méthode permettant de vérifier qu'un email est valide.
     * Un email est valide s'il est non vide, est une chaîne de caractères et contient plus de 5 caractères.
     * 
     * @param string $str L'email à vérifier
     * 
     * @return boolean True si l'email est valide, sinon False
     */
    private function isValidEmail($str)
    {
        $isValid = false;
        if (!empty(trim($str)) and is_string($str) and strlen($str) > 5) {
            $isValid = true;
        }
        return $isValid;
    }
    /**
     * Méthode permettant de vérifier qu'un mot de passe est valide.
     * Un mot de passe est valide s'il est non vide, est une chaîne de caractères et contient plus de 8 caractères.
     * 
     * @param string $password Le mot de passe à vérifier
     * 
     * @return boolean True si le mot de passe est valide, sinon False
     */
    private function isValidPassword($password)
    {
        $isValid = false;
        if (!empty(trim($password)) and is_string($password) and strlen($password) > 8) {
            $isValid = true;
        }
        return $isValid;
    }
    /**
     * Méthode permettant de vérifier qu'un champ est une chaîne non vide.
     * 
     * @param string $str Le champ à vérifier
     * 
     * @return boolean True si le champ est une chaîne non vide, sinon False
     */
    private function isValidString($str)
    {
        $isValid = false;
        if (!empty(trim($str)) and is_string($str)) {
            $isValid = true;
        }
        return $isValid;
    }
    /**
     * Méthode permettant de vérifier que tous les paramètres requis sont définis dans les données reçues.
     * 
     * @param array $params Un tableau contenant les noms des paramètres requis
     * @param array $data Un tableau contenant les données reçues
     * 
     * @return boolean True si tous les paramètres sont définis, sinon False
     */
    public function areParamsSet($params, $data)
    {
        $allSet = true;
        foreach ($params as $param) {
            if (!isset($data[$param])) {
                $allSet = false;
                break;
            }
        }
        return $allSet;
    }
}
