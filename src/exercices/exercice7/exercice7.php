<?php
	$bdd = new PDO('mysql:host=localhost;dbname=jeuxVideo151Ex7', 'root', 'root');
	$statement = $bdd->prepare("SELECT * FROM t_games");
	$statement->execute();
	$reponse = $statement->fetchAll();
	
	$i = 0;
	while (i<count($reponse))
	{
		echo $reponse[i];
		$i=$i+1;
	}
	$reponse->closeCursor();
?>
