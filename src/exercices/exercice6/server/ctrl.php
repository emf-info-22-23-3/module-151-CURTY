<?php
  class Ctrl{
    private $refWrk;
    public function __construct(){
      $this->refWrk = new Wrk();
    }
    function getEquipes(){
      return $this->refWrk->getEquipesFromDB();
    }
  }
?>