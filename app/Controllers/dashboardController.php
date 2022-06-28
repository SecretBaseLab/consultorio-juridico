<?php
namespace App\Controllers;

class dashboardController extends CoreController{
  public function getDashboardAction(){
    return $this->renderHTML('dashboard.twig',[
      'user_name' => $_SESSION['user_name']
    ]);
  }
}