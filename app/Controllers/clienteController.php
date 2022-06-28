<?php
namespace App\Controllers;

use App\Models\cliente;
use Laminas\Diactoros\Response\RedirectResponse;
use Respect\Validation\Validator as v;

class clienteController extends CoreController{
  public function getFormNuevoClienteAction(){
    return $this->renderHTML('nuevoCliente.twig');
  }

  public function postFormNuevoClienteAction($request){
    $responseMessage = null;    //var para recuperar los mesajes q suceda durante la ejecucion

    if ($request->getMethod() == "POST") {
        $postData = $request->getParsedBody();
        // print_r($postData);
        
        $usuariosValidator = v::key('cedula', v::stringType()->noWhitespace()->notEmpty())
        ->key('nombres', v::stringType()->notEmpty()->noWhitespace())
        ->key('apellidos', v::stringType()->notEmpty()->noWhitespace())
        ->key('direccion', v::stringType()->notEmpty()->noWhitespace())
       
        ->key('otros', v::stringType()->notEmpty()->noWhitespace());

        try {
            $usuariosValidator->assert($postData);   //? validando
            // v::each(v::stringType()->notEmpty()->noWhitespace()->phone())->validate($postData['telefono'])
            //verificando si ya existe ese usuario
            $cliente = new cliente();
            $existeCliente= $cliente
                        ->where("cedula", $postData['cedula'])
                        ->select('cedula')
                        ->first();
            if ( $existeCliente ) {
                $responseMessage = 'Este usuario ya esta registrado';
            }else{
                $cliente->cedula = $postData['cedula'];
                $cliente->nombres = $postData['nombres'];
                $cliente->apellidos = $postData['apellidos'];
                $cliente->telefono = $postData['telefono'];
                $cliente->correo = $postData['correo'];
                // $cliente->save();
                $responseMessage = 'Se ha guardado con éxito';
                // return new RedirectResponse('/dashboard');
            }
        } catch (\Exception $e) {
            $responseMessage = 'Ha ocurrido un error! ex001';
            $responseMessage = $e->getMessage();
        }
        
        // return $this->jsonReturn($responseMessage);
    }
    $assets = new assetsControler();
    return $this->renderHTML('nuevoCliente.twig', [
        'responseMessage' => $assets->alert($responseMessage, 'warning')
    ]);
  }
}