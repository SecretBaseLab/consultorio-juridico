<?php
namespace App\Controllers;

use App\Models\cliente;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Respect\Validation\Validator as v;
use Illuminate\Database\Capsule\Manager as Capsule;      //? conexion con la base de datos usando Query Builder
use Laminas\Diactoros\ServerRequest;

class expedienteController extends CoreController{

  public function get_form_nuevo_expediente_action(ServerRequest $request){
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

  public function post_form_nuevo_expediente_action(ServerRequest $request){
    $responseMessage = null;    //var para recuperar los mesajes q suceda durante la ejecucion
    $cedula = $request->getAttribute('cedula');

    if ($request->getMethod() == "POST") {
      $postData = $request->getParsedBody();
      
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
        
        if( !(v::number()->validate( $numero_expediente )) )
          $responseMessage = 'Datos del expediente no se pudo guardar<br>';

        $files = $request->getUploadedFiles();    //? captura datos de archivos subidos
        $files = $files['adjuntos_expediente'];
        assetsControler::upload_files(
          $files,
          'adjuntos_expediente',
          'numero_expediente',
          $numero_expediente,
          $datosExpediente['fecha_inicio_expediente'],
          $responseMessage
        );

        $this->notas_expediente_insert(
          $notas_expediente,
          $datosExpediente['fecha_inicio_expediente'],
          $numero_expediente,
          $responseMessage
        );
        
      } catch (\Exception $e) {
          $responseMessage = 'Ha ocurrido un error! ex001';
          // $responseMessage = $e->getMessage();
      }
    }
    // return $this->jsonReturn($files);
    if( $responseMessage == null )
      return new RedirectResponse("/cliente/$cedula");
    else{
      $cliente = cliente::where("cedula", $cedula)
                            ->get()->first();
      return $this->renderHTML('expedienteNuevo.twig', [
          'responseMessage' => assetsControler::alert($responseMessage, 'warning'),
          'cliente' => $cliente
      ]);
    }
  }

  /**
   * insertar una nota al expediente
   */
  private function notas_expediente_insert($notas_expediente, $created_at, $numero_expediente, &$responseMessage = null){
    foreach ($notas_expediente as $nota) {
      $response = Capsule::table('notas_expediente')->insert([
        'descripcion' => $nota,
        'created_at' => $created_at,
        'numero_expediente' => $numero_expediente
      ]);
      if( $response != 1)
        $responseMessage .= "Alguna nota no se pudo guardar. Revise!";
    }
  }

  public function get_expediente_action(ServerRequest $request){
    $numero_expediente = $request->getAttribute('numero_expediente');

    $expediente_local = Capsule::table('expediente_local')
    ->select('expediente_local.*', 'cliente.cedula', 'cliente.nombres', 'cliente.apellidos')
    ->join('cliente', 'cliente.cedula', '=', 'expediente_local.cedula')
    ->where("expediente_local.numero_expediente", "=", $numero_expediente)
    ->first();
    $notas_expediente = Capsule::table('notas_expediente')
    ->select('*')
    ->where("notas_expediente.numero_expediente", "=", $numero_expediente)
    ->latest('id_notas')
    ->get();
    $adjuntos_expediente = Capsule::table('adjuntos_expediente')
    ->select('*')
    ->where("adjuntos_expediente.numero_expediente", "=", $numero_expediente)
    ->latest('id_archivo')
    ->get();
    
    //? verificando si existe
    if( isset($expediente_local) ){
      return $this->renderHTML('expediente_local.twig',[
        'expediente_local' => $expediente_local,
        'notas_expediente' => $notas_expediente,
        'adjuntos_expediente' => $adjuntos_expediente
      ]);
    }else
      //? no existe
      return $this->renderHTML('404.twig');
  }

  /**
   * subir los archivos adjuntos_expediente
   */
  public function post_add_adjuntos_expediente_action(ServerRequest $request){
    $numero_expediente = $request->getAttribute('numero_expediente');

    if ($request->getMethod() == "POST") {
      $files = $request->getUploadedFiles();    //? captura datos de archivos subidos
        $files = $files['adjuntos_expediente'];
        assetsControler::upload_files(
          $files,
          'adjuntos_expediente',
          'numero_expediente',
          $numero_expediente,
          date('Y-m-d H-i-s')
        );
        return new RedirectResponse("/expediente/$numero_expediente");
    }
  }

  /**
   * actualiza el estado_expediente
   */
  public function put_estado_expediente_action(ServerRequest $request){
    $responseMessage = null;

    $numero_expediente = $request->getAttribute('numero_expediente');
    parse_str(file_get_contents("php://input"),$put_vars);    //? accedemos a la memoria de php para leer los datos mediante el metodo put
    $update = Capsule::table('expediente_local')
    ->where("numero_expediente", "=", $numero_expediente)
    ->update([
      "estado_expediente" => $put_vars['estado_expediente'],
      "updated_at" => Capsule::raw("NOW()")
    ]);
    if( $update == 1 )
      $responseMessage = "Se ha actualizado!";
    else
      $responseMessage = "Ha habido un error!";

    $respuesta = array(
      "alert" => assetsControler::alertAjax($responseMessage, 'info')
    );
    return $this->jsonReturn($respuesta);
  }

  public function post_add_notas_expediente_action(serverRequest $request){
    $responseMessage = null;

    if ($request->getMethod() == "POST") {
      $numero_expediente = $request->getAttribute('numero_expediente');
      $postData = $request->getParsedBody();
      $notas_expediente = $postData['notas_expediente'];

      try {
        v::each(v::stringType()->notEmpty())->validate($notas_expediente);  //? validando un array
        $this->notas_expediente_insert(
          $notas_expediente,
          Capsule::raw("NOW()"),
          $numero_expediente,
          $responseMessage
        );      
      } catch (\Exception $e) {
        $responseMessage = 'Ha ocurrido un error! ex001';
        // $responseMessage = $e->getMessage();
      }
      return new RedirectResponse("/expediente/$numero_expediente");

    }
  }
}