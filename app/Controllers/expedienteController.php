<?php
namespace App\Controllers;

use App\Models\cliente;
use App\Models\expediente_local;
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
      
      $datosCliente = array(
        'cedula' => $cedula,
        'tipo' => $postData['tipo'],
        'fecha_inicio_expediente' => $postData['fecha_inicio_expediente'],
        'otros' => $postData['otros']
      );
      $notas_expediente = $postData['notas_expediente'];

      //? inicio validacion de datos
      $usuariosValidator = v::key('cedula', v::stringType()->noWhitespace()->notEmpty())
      ->key('tipo', v::stringType()->notEmpty())
      ->key('fecha_inicio_expediente', v::stringType()->notEmpty())
      ->key('otros', v::stringType());

      try {
        $usuariosValidator->assert($datosCliente);   //? validando
        v::each(v::stringType()->notEmpty())->validate($notas_expediente);  //? validando un array
        //? fin de la validacion
 
        $files = $request->getUploadedFiles();    //? captura datos de archivos subidos
        $files = $files['adjuntos_expediente'];

        $numero_expediente = Capsule::table('expediente_local')->insertGetId([
          'cedula' => $datosCliente['cedula'],
          'tipo' => $datosCliente['tipo'],
          'otros' => $datosCliente['otros'],
          'created_at' => $datosCliente['fecha_inicio_expediente']
        ]);
        print_r($numero_expediente);
        /*
        foreach ($files as $file) {
          if($file->getError() == UPLOAD_ERR_OK ){
            $fileName = $file->getClientFilename();
            v::stringType()->notEmpty()->validate($fileName);   //? validando en nombre del archivo pa evitar inyeccion
            $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
            $fileName_hash = md5($fileName.date('Ymd')).'.'.$extension;
            $file->moveTo("uploads/adjuntos_expediente/$fileName_hash");
            
            $expediente_local = new expediente_local();
            $expediente_local->fileName = $fileName;
            $expediente_local->fileName_hash = $fileName_hash;
            $expediente_local->extension = $extension;
            $expediente_local->numero_expediente = $numero_expediente;
            $expediente_local->created_at = $datosCliente['fecha_inicio_expediente'];
            $expediente_local->save();

           
          }
        }
        
  */
        
        
  /*
        if($expediente_local->save() != 1)
          $responseMessage = 'Datos del cliente no se pudo guardar<br>';

        foreach ($correosCliente as $correo) {
          $correo_cliente = new correo_cliente();
          $correo_cliente->correo = $correo;
          $correo_cliente->cedula = $datosCliente['cedula'];

          $id = DB::table('users')->insertGetId(
            ['email' => 'john@example.com', 'votes' => 0]
          );

          if($correo_cliente->save() != 1)
            $responseMessage .= "No se pudo guardar: $correo <br>";
        }*/

        
        // print_r($postData);
        // print_r($files);
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