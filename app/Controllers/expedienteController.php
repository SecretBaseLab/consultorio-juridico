<?php
namespace App\Controllers;

use App\Models\cliente;
use App\Models\expediente_local;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Respect\Validation\Validator as v;

class expedienteController extends CoreController{

  public function get_form_nuevo_expediente_action($request){
    $cedula = $request->getAttribute('cedula');

    $cliente = cliente::where("cedula", $cedula)
                            ->get();
    //? verificando si existe el cliente
    if( isset($cliente[0]) ){
      return $this->renderHTML('expedienteNuevo.twig',[
        'cliente' => $cliente[0]    //? devuelve un obj de arrays, solo quiero el primero o bien usar first() por get()
      ]);
    }else
      // el cliente no existe
      return $this->renderHTML('404.twig');
  }

  public function post_form_nuevo_expediente_action($request){
    $responseMessage = null;    //var para recuperar los mesajes q suceda durante la ejecucion

    if ($request->getMethod() == "POST") {
      $postData = $request->getParsedBody();
      $cedula = $request->getAttribute('cedula');
      
      $datosCliente = array(
        'cedula' => $cedula,
        'tipo' => $postData['tipo'],
        'fecha_inicio_expediente' => $postData['fecha_inicio_expediente']
      );
      $notas_expediente = $postData['notas_expediente'];
      
      $usuariosValidator = v::key('cedula', v::stringType()->noWhitespace()->notEmpty())
      ->key('tipo', v::stringType()->notEmpty())
      ->key('fecha_inicio_expediente', v::stringType()->notEmpty());

      try {
        $usuariosValidator->assert($datosCliente);   //? validando
        //? validando un array
        v::each(v::stringType()->notEmpty())->validate($notas_expediente);
/*
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
          $cliente->tipo = $datosCliente['tipo'];
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
        */
      } catch (\Exception $e) {
          $responseMessage = 'Ha ocurrido un error! ex001';
          // $responseMessage = $e->getMessage();
      }
      
    }
    
    $assets = new assetsControler();
    return $this->renderHTML('expedienteNuevo.twig', [
        'responseMessage' => $assets->alert($responseMessage, 'warning')
    ]);
  }
}