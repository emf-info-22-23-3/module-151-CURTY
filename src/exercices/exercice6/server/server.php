<?php
include_once("Wrk.php");
include_once("Ctrl.php");
$ctrl = new Ctrl();
if (isset($_GET['action']) && $_GET['action'] == 'getTeams') {
    $teams = $ctrl->getEquipes();
    echo json_encode($teams);
} else {
    echo "No valid parameter was found !";
}
?>