<?php
namespace App\Controllers;

use App\Models\cliente;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Respect\Validation\Validator as v;
use Illuminate\Database\Capsule\Manager as Capsule;      //conexion con la base de datos usando Query Builder



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
      
      $datosExpediente = array(
        'cedula' => $cedula,
        'tipo' => $postData['tipo'],
        'fecha_inicio_expediente' => $postData['fecha_inicio_expediente'],
        'otros' => $postData['otros']
      );
      $notas_expediente = $postData['notas_expediente'];

      //? inicio validacion de datos
      $validator = v::key('cedula', v::stringType()->noWhitespace()->notEmpty())
      ->key('tipo', v::stringType()->notEmpty())
      ->key('fecha_inicio_expediente', v::stringType()->notEmpty())
      ->key('otros', v::stringType());

      try {
        $validator->assert($datosExpediente);   //? validando
        v::each(v::stringType()->notEmpty())->validate($notas_expediente);  //? validando un array
        //? fin de la validacion

        $numero_expediente = Capsule::table('expediente_local')->insertGetId([
          'cedula' => $datosExpediente['cedula'],
          'tipo' => $datosExpediente['tipo'],
          'otros' => $datosExpediente['otros'],
          'created_at' => $datosExpediente['fecha_inicio_expediente']
        ]);
        
        if( $numero_expediente != 1 )
          $responseMessage = 'Datos del expediente no se pudo guardar<br>';
        assetsControler::adjuntos_expediente_insert($request, $numero_expediente, $datosExpediente['fecha_inicio_expediente']);

        foreach ($notas_expediente as $nota) {
          Capsule::table('notas_expediente')->insert([
            '`descripcion`' => $nota,
            'created_at' => $datosExpediente['fecha_inicio_expediente'],
            'numero_expediente' => $numero_expediente
          ]);
        }
      } catch (\Exception $e) {
          $responseMessage = 'Ha ocurrido un error! ex001';
          $responseMessage = $e->getMessage();
      }
      
    }
    // return $this->jsonReturn($files);
    $assets = new assetsControler();
    return $this->renderHTML('expedienteNuevo.twig', [
        'responseMessage' => $assets->alert($responseMessage, 'warning')
    ]);
  }
}