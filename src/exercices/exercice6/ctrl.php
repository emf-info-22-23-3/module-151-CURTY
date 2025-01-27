<?php
  class Ctrl{
    private $refWrk;
    public function __construct(){
      include_once('Wrk.php');
      $this->refWrk = new Wrk();
    }
    function getEquipes(){
      return $this->refWrk->getEquipesFromDB();
    }
  }
?>