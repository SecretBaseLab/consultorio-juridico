<?php
namespace App\Controllers;

class assetsControler{
    public function alert($message, $typeAlert='info', $icon=''){
        if($message==NULL) return '';
        
        return '<div class="alert alert-'.$typeAlert.' alert-dismissible fade show" role="alert">
                    '.$icon.'
                    <div class="d-inline ms-1">
                        '.$message.'
                    </div>
                    <button id="btn_close_alert" type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script> window.onload = ()=>{ setTimeout(() => {btn_close_alert.click()}, 2500); }</script>';
    }

    public function alertAjax($message, $typeAlert='info', $icon=''){
        if($message==NULL) return '';
        
        return '<div class="alert alert-'.$typeAlert.' alert-dismissible fade show" role="alert">
                    '.$icon.'
                    <div class="d-inline ms-1">
                        '.$message.'
                    </div>
                    <button id="btn_close_alert" type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }

    /**
    * subir archivos a adjuntos_expediente
    */
    public static function adjuntos_expediente_insert($request, $numero_expediente, $fecha_inicio_expediente){
        $files = $request->getUploadedFiles();    //? captura datos de archivos subidos
        $files = $files['adjuntos_expediente'];
    
        foreach ($files as $file) {
          if($file->getError() == UPLOAD_ERR_OK ){
            $fileName = $file->getClientFilename();
            v::stringType()->notEmpty()->validate($fileName);   //? validando en nombre del archivo pa evitar inyeccion
            $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
            $fileName_hash = md5($fileName.date('Ymd')).'.'.$extension;
            $file->moveTo("uploads/adjuntos_expediente/$fileName_hash");
            
            $response = Capsule::table('adjuntos_expediente')->insert([
              'fileName' => $fileName,
              'fileName_hash' => $fileName_hash,
              'extension' => $extension,
              'numero_expediente' => $numero_expediente,
              'created_at' => $fecha_inicio_expediente
            ]);
            
          }
        }
      }
}