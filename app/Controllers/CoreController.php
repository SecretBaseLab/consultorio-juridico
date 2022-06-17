<?php
namespace App\Controllers;

use Laminas\Diactoros\Response\HtmlResponse;    //llamando al metodo HTMLresponse

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
}