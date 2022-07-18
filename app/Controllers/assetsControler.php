<?php
namespace App\Controllers;

use Respect\Validation\Validator as v;
use Illuminate\Database\Capsule\Manager as Capsule;      //? conexion con la base de datos usando Query Builder

class assetsControler{
    public static function alert($message, $typeAlert='info', $icon=''){
        if($message==NULL) return '';
        
        return '<div class="alert alert-'.$typeAlert.' alert-dismissible fade show" role="alert">
                    '.$icon.'
                    <div class="d-inline ms-1">
                        '.$message.'
                    </div>
                    <button id="btn_close_alert" type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <script> window.onload = ()=>{ setTimeout(() => {btn_close_alert.click()}, 2600); }</script>';
    }

    public static function alertAjax($message, $typeAlert='info', $icon=''){
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
    * subir archivos
    * @param array $files Array de archivos
    * @param string $directorio ruta donde se va a guardar, coincide nombre de la tabla
    * @param string $clave_foranea_name nombre de la clave foranea
    * @param int $clave_foranea_value valor de la calve foranea
    * @param date $created_at fecha de creacion del archivo
    * @param string $responseMessage Mensaje de respuesta para recolectar si hay algun error, se recibe mediante puntero
    */
    public static function upload_files($files, $directorio, $clave_foranea_name, $clave_foranea_value, $created_at, &$responseMessage = null){   
        foreach ($files as $file) {
            if($file->getError() == UPLOAD_ERR_OK ){
                $fileName = $file->getClientFilename();
                v::stringType()->notEmpty()->validate($fileName);   //? validando en nombre del archivo pa evitar inyeccion
                $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
                $fileName_hash = md5($fileName.date('Ymd')).'.'.$extension;
                $file->moveTo("uploads/$directorio/$fileName_hash");
                
                $response = Capsule::table($directorio)->insert([
                    'file_name' => $fileName,
                    'file_name_hash' => $fileName_hash,
                    'extension' => $extension,
                    "$clave_foranea_name" => $clave_foranea_value,
                    'created_at' => $created_at
                ]);

                if( $response != 1)
                    $responseMessage .= "No se pudo subir: $fileName";
            }
        }
    }
}