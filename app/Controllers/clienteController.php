<?php
namespace App\Controllers;

use App\Models\cliente;
use App\Models\correo_cliente;
use App\Models\telefono_cliente;
use Laminas\Diactoros\Response\JsonResponse;
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
      $datosCliente = array(
        'cedula' => $postData['cedula'],
        'nombres' => $postData['nombres'],
        'apellidos' => $postData['apellidos'],
        'direccion' => $postData['direccion'],
        'otros' => $postData['otros']
      );
      $telefonosCliente = $postData['telefono'];
      $correosCliente = $postData['correo'];
      // print_r($postData);
      
      $validator = v::key('cedula', v::stringType()->noWhitespace()->notEmpty())
      ->key('nombres', v::stringType()->notEmpty())
      ->key('apellidos', v::stringType()->notEmpty())
      ->key('direccion', v::stringType()->notEmpty())
      ->key('otros', v::stringType());

      try {
        $validator->assert($datosCliente);   //? validando
        //? validando un array
        v::each(v::stringType()->notEmpty()->noWhitespace()->phone())->validate($telefonosCliente);
        v::each(v::stringType()->notEmpty()->noWhitespace()->email())->validate($correosCliente);

        //verificando si ya existe ese usuario
        $cliente = new cliente();
        $existeCliente= $cliente
                    ->where("cedula", $datosCliente['cedula'])
                    ->select('cedula')
                    ->first();
        if ( $existeCliente ) {
            $responseMessage = 'Este usuario ya esta registrado';
        }else{
          $cliente->cedula = $datosCliente['cedula'];
          $cliente->nombres = $datosCliente['nombres'];
          $cliente->apellidos = $datosCliente['apellidos'];
          $cliente->direccion = $datosCliente['direccion'];
          $cliente->otros = $datosCliente['otros'];
          if($cliente->save() != 1)
            $responseMessage = 'Datos del cliente no se pudo guardar<br>';

          foreach ($correosCliente as $correo) {
            $correo_cliente = new correo_cliente();
            $correo_cliente->correo = $correo;
            $correo_cliente->cedula = $datosCliente['cedula'];

            if($correo_cliente->save() != 1)
              $responseMessage .= "No se pudo guardar: $correo <br>";
          }

          foreach ($telefonosCliente as $telefono) {
            $telefono_cliente = new telefono_cliente();
            $telefono_cliente->numero = $telefono;
            $telefono_cliente->cedula = $datosCliente['cedula'];

            if($telefono_cliente->save() != 1)
              $responseMessage .= "No se pudo guardar: $telefono <br>";
          }
        }
      } catch (\Exception $e) {
          $responseMessage = 'Ha ocurrido un error! ex001';
          // $responseMessage = $e->getMessage();
      }
      
      // return $this->jsonReturn($responseMessage);
    }
    
    if($responseMessage == null){
      $responseMessage = 'Todos los datos se han guardado';
      // return new RedirectResponse("/cliente/$datosCliente[cedula]");
      //? proseso para ajax
      $respuesta = array(
        'responseMessage' => $responseMessage,
        'redirect_path' => "/cliente/$datosCliente[cedula]"
      );
    }else{
      $assets = new assetsControler();
      
      //? proseso para ajax
      $responseMessage = $assets->alertAjax($responseMessage, 'warning');
      $respuesta = array(
        'responseMessage' => $responseMessage,
        'redirect_path' => ""
      );
    }
    return $this->jsonReturn(json_encode($respuesta));
  }

  public function getClienteAction($request){
    $cedula = $request->getAttribute('cedula');

    $cliente = cliente::where("cedula", $cedula)
                            ->get();
    // print( "<pre>". print_r($cliente, true)."</pre>" );

    //? verificando si existe el cliente
    if( isset($cliente[0]) ){
      $telefono_cliente = telefono_cliente::where("cedula", $cedula)
                              ->get();
      $correo_cliente = correo_cliente::where("cedula", $cedula)
                              ->get();
      return $this->renderHTML('cliente.twig',[
        'cliente' => $cliente[0],    //? devuelve un obj de arrays, solo quiero el primero o bien usar first() por get()
        'telefono_cliente' => $telefono_cliente,
        'correo_cliente' => $correo_cliente
      ]);
    }else
      // el cliente no existe
      return $this->renderHTML('404.twig');
  }
}