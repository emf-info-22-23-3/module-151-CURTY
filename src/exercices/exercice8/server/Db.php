<?php
    class Db{
        public $bdd;
        public function __construct(){
            $this->bdd = new PDO('mysql:host=database;dbname=hockey_stats', 'root', 'root');
          }
        public function getPlayers($playersId){
	        $statement = $this->bdd->prepare("SELECT * FROM t_joueur WHERE FK_equipe={$playersId}");
	        $statement->execute();
	        $reponse = $statement->fetchAll();
            $statement->closeCursor();
            return $reponse;
        }
        public function getTeams(){
	        $statement = $this->bdd->prepare('SELECT * FROM t_equipe');
	        $statement->execute();
	        $reponse = $statement->fetchAll();
            $statement->closeCursor();
            return $reponse;
        }
    }

?>