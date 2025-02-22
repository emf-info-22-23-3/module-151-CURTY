<?php
class UserCtrl
{
    private $workerAuthentication;

    public function __construct()
    {
        $this->workerAuthentication = new WorkerAuthentication();
    }

    public function authenticateUser($email, $password)
    {
        $toReturn = NULL;
        if ($this->isValidEmail($email) and $this->isValidString($password)) {
            $toReturn = $this->workerAuthentication->authenticateUser($email, $password);
        } else {
            $toReturn = BAD_REQUEST;
        }
        return $toReturn;
    }
    public function registerUser($name, $familyName, $email, $password)
    {
        $toReturn = NULL;
        $toReturn = $this->workerAuthentication->register($name, $familyName, $email, $password);
        return $toReturn;
    }

    /**
     * Méthode permettant de vérifier qu'un champ est belle est bien un string et qu'il contienne plus que 5 caractères.
     * 
     * @param $str Le champ a vérifier
     * @return boolean si le champ est un string valide ou non
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
     * Méthode permettant de vérifier si un password est valide ou non.
     * 
     * @param $password Le password a vérifier
     * 
     * @return boolean si le password est valide ou non
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
    /**
     * Méthode permettant de vérifier que les paramètres soient bien définit
     * 
     * @param $params Un tableau avec les paramètres a vérifier
     * @param $data Un tableau qui contient les données recues
     * 
     * @return Boolean qui indique si tous les paramtères sont set ou non
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
