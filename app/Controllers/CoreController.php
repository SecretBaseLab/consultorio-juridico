<?php
namespace App\Controllers;

use Laminas\Diactoros\Response\HtmlResponse;    //llamando al metodo HTMLresponse
use Laminas\Diactoros\Response\JsonResponse;     //metodo pa devolver json al cliente, util pa ajax

class CoreController{
    protected $templateEngine; //? var q almacena la instancia del motor del plantillas

    public function __construct(){
        $loader = new \Twig\Loader\FilesystemLoader('../views');    //carga la rutas de las plantillas
        $this->templateEngine = new \Twig\Environment($loader, [
            'debug' => true,
            'cache' => false,
        ]);
    }

    public function renderHTML($fileName, $data = [] ){
        return new HtmlResponse( $this->templateEngine->render($fileName, $data) ); //envia el html al cliente rellenando los datos
    }

    /**
     * retorna al cliente un json
     */
    public function jsonReturn($data){
        return new JsonResponse($data);
    }
}