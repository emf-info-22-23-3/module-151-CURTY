<?php 
	include_once('Connexion.php');
	include_once('beans/Pays.php');
        
	/**
	* Classe paysBDManager
	*
	* Cette classe permet la gestion des pays dans la base de données dans l'exercice de debbugage
	*
	*/
	class PaysBDManager
	{
		/**
		* Fonction permettant la lecture des pays.
		* Cette fonction permet de retourner la liste des pays se trouvant dans la liste
		*
		* @return liste de Pays
		*/
		public function readPays()
		{
			$count = 0;
			$liste = array();
			$connection = Connexion::getInstance();
			$query = $connection->selectQuery("select * from t_pays order by Nom", array());
			foreach($query as $data){
				$pays = new Pays($data['PK_pays'], $data['Nom']);
				$liste[$count++] = $pays;
			}	
			return $liste;	
		}
		
		/**
		* Fonction permettant de retourner la liste des pays en XML.
		*
		* @return String. Liste des pays en XML
		*/
		public function getInXML()
		{
			$listPays = $this->readPays();
			$result = '<listePays>';
			for($i=0;$i<sizeof($listPays);$i++) 
			{
				$result = $result .$listPays[$i]->toXML();
			}
			$result = $result . '</listePays>';
			return $result;
		}
	}
?>