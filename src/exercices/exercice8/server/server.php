<?php
	$bdd = new PDO('mysql:host=database;dbname=jeuxVideo151Ex7', 'root', 'root');
	$statement = $bdd->prepare('SELECT * FROM t_games');
	$statement->execute();
	$reponse = $statement->fetchAll();
	
	$i = 0;
	while ($i<count($reponse))
	{
		print_r($reponse[$i]) ;
		$i=$i+1;
	}
	$statement->closeCursor();
?>