<?php
class WorkerAuthentication
{
    private static $_instance = null;
    private $userDenied;
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
            self::$_instance = new WorkerAuthentication();
        }
        return self::$_instance;
    }

    /**
     * Méthode qui va initialiser les attributs de la classe
     */
    private function __construct()
    {
        $this->userDenied = new ErrorAnswer("The provided login/password does not match.", 401);
        $this->db = Connexion::getInstance();
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
}
