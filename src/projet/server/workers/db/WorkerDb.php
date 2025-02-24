<?php
include_once('configConnexion.php');
/**
 * Classe WorkerDb
 *
 * Cette classe gère les connexions à la base de données et l'exécution des requêtes SQL.
 * Elle suit le modèle Singleton pour garantir qu'il n'y ait qu'une seule instance de la connexion.
 * Elle inclut des méthodes pour exécuter des requêtes SELECT, INSERT, UPDATE, DELETE.
 *
 * @version 1.0
 * @author Curty Esteban
 * @project BaoBull
 * 
 * @uses configConnexion
 */

class WorkerDb
{

    private static $_instance = null;
    private $pdo;
    private $dbError;

    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @return WorkerDb L'instance de la connexion à la base de données
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new WorkerDb();
        }
        return self::$_instance;
    }

    /**
     * Constructeur privé pour ouvrir une connexion à la base de données.
     * Utilise les constantes définies dans configConnexion.php pour établir la connexion.
     */
    private function __construct()
    {
        try {
            $this->pdo = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::ATTR_PERSISTENT => true
            ));
            $this->dbError = new ErrorAnswer("Error while trying to access the database. Please try again in a moment.", 500);
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * Destructeur qui ferme la connexion à la base de données lorsque l'objet WorkerDb est détruit.
     */
    public function __destruct()
    {
        $this->pdo = null;
    }

    /**
     * Exécute une requête SELECT dans la base de données et retourne toutes les lignes du résultat.
     *
     * @param string $query La requête SQL à exécuter
     * @param array $params Les paramètres à lier à la requête (null si aucun paramètre n'est requis)
     * @return array|ErrorAnswer Un tableau contenant toutes les lignes du résultat ou une erreur si une exception est levée
     */
    public function selectQuery($query, $params)
    {
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute($params);
            return $queryPrepared->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return $this->dbError;
        }
    }

    /**
     * Exécute une requête SELECT dans la base de données et retourne la première ligne du résultat.
     *
     * @param string $query La requête SQL à exécuter
     * @param array $params Les paramètres à lier à la requête (null si aucun paramètre n'est requis)
     * @return array|ErrorAnswer Un tableau contenant la première ligne du résultat ou une erreur si une exception est levée
     */
    public function selectQuerySingleResult($query, $params)
    {
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryPrepared->execute($params);
            return $queryPrepared->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return $this->dbError;
        }
    }

    /**
     * Exécute une requête SQL (UPDATE, DELETE, INSERT) dans la base de données.
     *
     * @param string $query La requête SQL à exécuter
     * @param array $params Les paramètres à lier à la requête (null si aucun paramètre n'est requis)
     * @return bool|ErrorAnswer Retourne true si la requête a été exécutée avec succès, sinon une erreur
     */
    public function executeQuery($query, $params)
    {
        try {
            $queryPrepared = $this->pdo->prepare($query);
            $queryRes = $queryPrepared->execute($params);
            return $queryRes;
        } catch (PDOException $e) {
            return $this->dbError;
        }
    }
    /**
     * Récupère le dernier ID inséré dans la base de données.
     *
     * @param string $table Le nom de la table où l'insertion a eu lieu
     * @return int|ErrorAnswer Retourne l'ID du dernier élément inséré ou une erreur si une exception est levée
     */
    public function getLastId($table)
    {
        try {
            $lastId = $this->pdo->lastInsertId($table);
            return $lastId;
        } catch (PDOException $e) {
            return $this->dbError;
        }
    }
}
