<?php
namespace App\Controllers;

use App\Models\cliente;
use App\Models\correo_cliente;
use App\Models\telefono_cliente;
use Laminas\Diactoros\Response\RedirectResponse;
use Respect\Validation\Validator as v;
use Illuminate\Database\Capsule\Manager as Capsule;      //? conexion con la base de datos usando Query Builder
use Laminas\Diactoros\ServerRequest;

class clienteController extends CoreController{
  /**
   * muestra el formulario para registrar un nuevo cliente
   */
  public function getFormNuevoClienteAction(){
    return $this->renderHTML('nuevoCliente.twig');
  }
  /**
   * guarda un cliente
   */
  public function postFormNuevoClienteAction(ServerRequest $request){
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
      //? proseso para ajax
      $responseMessage = assetsControler::alertAjax($responseMessage, 'warning');
      $respuesta = array(
        'responseMessage' => $responseMessage,
        'redirect_path' => ""
      );
    }
    return $this->jsonReturn(json_encode($respuesta));
  }
  /**
   * muestra la info del cliente y sus expedientes
   */
  public function getClienteAction(ServerRequest $request){
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
      $expediente_local = Capsule::table('expediente_local')
      ->select('numero_expediente', 'tipo','estado_expediente','created_at')
      ->where("expediente_local.cedula", "=", $cedula)
      ->latest('numero_expediente')
      ->get();
      return $this->renderHTML('cliente.twig',[
        'cliente' => $cliente[0],    //? devuelve un obj de arrays, solo quiero el primero o bien usar first() por get()
        'telefono_cliente' => $telefono_cliente,
        'correo_cliente' => $correo_cliente,
        'expediente_local' => $expediente_local
      ]);
    }else
      // el cliente no existe
      return $this->renderHTML('404.twig');
  }
  /**
   * muestra el formulario para buscar y una lista de los ultimos clientes registrados
   */
  public function search_cliente_get_form_action(){
    $clientes = Capsule::table('cliente')
    ->select('cliente.cedula', 'nombres', 'apellidos', 'created_at')
    ->latest('cliente.created_at')
    ->limit(10)
    ->get();
    return $this->renderHTML('cliente_buscar.twig',[
      'clientes' => $clientes
    ]);
  }

  /**
   * busca clientes por el numero de cedula
   */
  public function search_cliente_post_action(ServerRequest $request){
    $responseMessage = null;    //var para recuperar los mesajes q suceda durante la ejecucion

    if ($request->getMethod() == "POST") {
      $postData = $request->getParsedBody();
      $cedula = $postData['cedula'];
      // Capsule::statement("SET lc_time_names = 'es_EC'");
      $clientes = Capsule::table('cliente')
      ->select('cedula', 'nombres', 'apellidos', Capsule::raw('DATE_FORMAT(created_at, "%d-%m-%Y") as created_at'))
      ->where('cedula', 'like', "$cedula%")
      ->latest('created_at')
      ->limit(10)
      ->get();
      return $this->jsonReturn($clientes);
    
    }
  }
}