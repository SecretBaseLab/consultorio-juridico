<?php
namespace App\Controllers;

use App\Models\cliente;
use Laminas\Diactoros\Response\RedirectResponse;
use Respect\Validation\Validator as v;

class clienteController extends CoreController{
  public function getFormNuevoCliente(){
    return $this->renderHTML('nuevoCliente.twig');
  }
}