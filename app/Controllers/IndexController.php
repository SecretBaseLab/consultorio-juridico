<?php
namespace App\Controllers;

class IndexController extends CoreController{
    public function indexAction(){
        return $this->renderHTML('index.twig');
    }
}