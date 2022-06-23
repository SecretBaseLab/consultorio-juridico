<?php
namespace App\Controllers;

class dashboardController extends CoreController{
  public function getDashboardAction(){
    return $this->renderHTML('dashboard.twig');
  }
}