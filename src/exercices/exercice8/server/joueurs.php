<?php
include_once("Db.php");
$db = new Db();
if($_GET['action'] == "equipe")
{
	$teams = $db->getTeams();
	echo '<equipes>';

	for ($i=0; $i < count($teams); $i++) { 
		$team = $teams[$i];
		echo "<equipe><id>{$i}</id><nom>{$team['Nom']}</nom></equipe>";
	}
	echo '</equipes>';
}
if($_GET['action'] == "joueur")
{
	echo '<joueurs>';
	$equipeId = $_GET['equipeId'];
	$players = $db->getPlayers($equipeId);
	for ($i=0; $i < count($players); $i++) { 
		$player = $players[$i];
		echo "<joueur><id>{$i}</id><nom>{$player['Nom']}</nom><points>{$player['Points']}</points></joueur>";
	}
	echo '</joueurs>';
}

?>