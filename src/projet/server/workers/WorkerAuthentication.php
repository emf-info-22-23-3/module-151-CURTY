<?php

/**
 * Classe WorkerAuthentication
 *
 * Cette classe gère l'authentification des utilisateurs et leur l'inscription.
 * @version 1.0
 * @author Curty Esteban
 * @project BaoBull
 */
class WorkerAuthentication
{
    private $userDenied;
    private $db;


    /**
     * Constructeur de la classe WorkerAuthentication.
     * Initialise l'objet d'erreur pour les tentatives de connexion échouées et la connexion à la base de données.
     */
    public function __construct()
    {
        $this->userDenied = new ErrorAnswer("The provided login/password does not match.", 401);
        $this->db = WorkerDb::getInstance();
    }

    /**
     * Authentifie un utilisateur en vérifiant l'email et le mot de passe.
     *
     * @param string $email L'adresse email de l'utilisateur.
     * @param string $password Le mot de passe de l'utilisateur.
     * @return User|ErrorAnswer Un objet User si l'authentification réussit, ou une ErrorAnswer si l'authentification échoue.
     */
    public function authenticateUser($email, $password)
    {
        $query = "SELECT pk_user, name, familyName, email, password FROM t_user WHERE email = :email";
        $params = array('email' => $email);
        $user = $this->db->selectQuerySingleResult($query, $params);
        $final = NULL;
        if ($user && !($user instanceof ErrorAnswer) and password_verify($password, $user['password'])) {
            $final = new User($user["name"], $user["familyName"], $user["email"], $user["pk_user"]);
            $final->setIsAuthenticated(TRUE);
        } else {
            $final = $this->userDenied;
        }
        return $final;
    }
    /**
     * Vérifie si un email est déjà utilisé par un autre utilisateur dans la base de données.
     *
     * @param string $email L'adresse email à vérifier.
     * @return bool|ErrorAnswer Retourne TRUE si l'email existe, FALSE sinon, ou une ErrorAnswer en cas d'erreur.
     */
    private function emailAlreadyExists($email)
    {
        $query = "SELECT pk_user FROM t_user WHERE email = :email";
        $params = array('email' => $email);
        $pkuser = $this->db->selectQuerySingleResult($query, $params);
        $exists = FALSE;
        if ($pkuser instanceof ErrorAnswer) {
            $exists = $pkuser;
        } else if ($pkuser) {
            $exists = TRUE;
        }
        return $exists;
    }
    /**
     * Crée un nouveau compte utilisateur dans la base de données.
     *
     * @param string $name Le prénom de l'utilisateur.
     * @param string $familyName Le nom de famille de l'utilisateur.
     * @param string $email L'adresse email de l'utilisateur.
     * @param string $password Le mot de passe de l'utilisateur.
     * @return User|ErrorAnswer Un objet User avec les informations de l'utilisateur si l'inscription réussit, 
     *                           ou une ErrorAnswer si une erreur survient.
     */
    public function register($name, $familyName, $email, $password)
    {
        $toReturn = NULL;
        $emailExists = $this->emailAlreadyExists($email);
        if ($emailExists instanceof ErrorAnswer) {
            $toReturn = $emailExists;
        } else if ($email == 1) {
            $toReturn = new ErrorAnswer("The provided email is already linked with another account.", 409);
        } else {
            $query = "INSERT INTO t_user (name, familyName, email, password)VALUES (:name, :famName, :email, :password)";
            $params = array(":name" => $name, ":famName" => $familyName, ":email" => $email, ":password" => password_hash($password, PASSWORD_DEFAULT));
            $affectedRows = $this->db->executeQuery($query, $params);
            if ($affectedRows instanceof ErrorAnswer) {
                $toReturn = $affectedRows;
            } else if ($affectedRows and $affectedRows == 1) {
                $toReturn = $this->authenticateUser($email, $password);
            } else {
                $toReturn = new ErrorAnswer("Unfortunately the server was not able to create the account.", 500);
            }
        }
        return $toReturn;
    }
}
