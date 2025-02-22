<?php
class WorkerAuthentication
{
    private $userDenied;
    private $db;


    /**
     * Méthode qui va initialiser les attributs de la classe
     */
    public function __construct()
    {
        $this->userDenied = new ErrorAnswer("The provided login/password does not match.", 401);
        $this->db = WorkerDb::getInstance();
    }

    /**
     * Fonction permettant d'authentifier un utilisateur
     * @param email L'addresse email de l'utilisateur
     * @param password Password de l'utilisateur
     * 
     * @return un objet User avec ces informations ou une erreur si l'authentification a échouée
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
     * Méthode permettant de créer une nouveau compte utilisateur dans la DB
     * 
     * @param $name le nom de l'utilisateur
     * @param $familyName le nom de famille de l'utilisateur
     * @param $email l'adresse email de l'utilisateur
     * @param $password le mot de passe de l'utilisateur
     * 
     * @return User un objet de type user avec les inforamtions sur celui-ci
     */
    public function register($name, $familyName, $email, $password)
    {
        $query = "INSERT INTO t_user (name, familyName, email, password)VALUES (:name, :famName, :email, :password)";
        $params = array(":name" => $name, ":famName" => $familyName, ":email" => $email, ":password" => password_hash($password, PASSWORD_DEFAULT));
        $affectedRows = $this->db->executeQuery($query, $params);
        $toReturn = NULL;
        if ($affectedRows instanceof ErrorAnswer) {
            $toReturn = $affectedRows;
        } else if ($affectedRows and $affectedRows == 1) {
            $toReturn = $this->authenticateUser($email, $password);
        } else {
            $toReturn = new ErrorAnswer("Unfortunately the server was not able to create the account.", 500);
        }
        return $toReturn;
    }
}
