<?php
namespace App\Controllers;

use App\Models\passMaster;
use App\Models\usuarios;
use Respect\Validation\Validator as v;
use Laminas\Diactoros\Response\RedirectResponse;


class loginController extends CoreController{
    /**
     * funcion para generar una vista 404 para paginas no encontradas
     */
    public function Action_404(){
        return $this->renderHTML('404.twig');
    }

    public function geFormLoginAction(){
        if ( isset($_SESSION['user_name']) )
            return new RedirectResponse('/dashboard');
        else
            return $this->renderHTML('index.twig');
    }

    public function getSignUpAction(){
        //?si esta logeado y es admin puede acceder
      if ( isset($_SESSION['user_name']) && $_SESSION['rol']=='Admin' )
        return $this->renderHTML('signup.twig');
      else
        return new RedirectResponse('/dashboard');
    }
    
    public function postLoginAction($request){
        $responseMessage = null;    //var para recuperar los mesajes q suceda durante la ejecucion

        if ($request->getMethod() == "POST") {
            $postData = $request->getParsedBody();
            $usuariosValidator = v::key('user_name', v::stringType()->noWhitespace()->notEmpty())
            ->key('password', v::stringType()->notEmpty()->noWhitespace());

            try {
                $usuariosValidator->assert($postData);   //? validando

                $usuario = new usuarios();
                $existeusuario = $usuario
                                ->where("user_name", $postData['user_name'])
                                ->first();
                if( $existeusuario )
                    if ( password_verify( $postData['password'], $existeusuario->password) ){
                        $_SESSION['user_name'] = $postData['user_name'];
                        $_SESSION['rol'] = $existeusuario->rol;
                        return new RedirectResponse('/dashboard');
                    }else{
                        $responseMessage = 'Credenciales incorrectas o el usuario no existe';
                    }
                else
                    $responseMessage = 'Credenciales incorrectas o el usuario no existe';
                            
            } catch (\Exception $e) {
                $responseMessage = 'Credenciales incorrectas o el usuario no existe';
                // $responseMessage = $e->getMessage();
            }
        }

        //mensaje de retorno a la misma vista con un alert
        $assets = new assetsControler();
        return $this->renderHTML('index.twig', [
            'responseMessage' => $assets->alert($responseMessage, 'warning')
        ]);
    }

    public function postSignUpAction($request){
        $responseMessage = null;    //var para recuperar los mesajes q suceda durante la ejecucion

        if ($request->getMethod() == "POST") {
            $postData = $request->getParsedBody();
            
            $usuariosValidator = v::key('cedula', v::stringType()->noWhitespace()->notEmpty())
            ->key('rol', v::stringType()->notEmpty()->noWhitespace())
            ->key('nombres', v::stringType()->notEmpty()->noWhitespace())
            ->key('apellidos', v::stringType()->notEmpty()->noWhitespace())
            ->key('telefono', v::stringType()->notEmpty()->noWhitespace()->phone())
            ->key('email', v::stringType()->notEmpty()->noWhitespace()->email())
            ->key('user_name', v::stringType()->notEmpty()->noWhitespace())
            ->key('password', v::stringType()->notEmpty()->noWhitespace())
            ->key('passMaster', v::stringType()->notEmpty()->noWhitespace());
            

            try {
                $usuariosValidator->assert($postData);   //? validando

                //verificacion del passMaster
                $passMaster = new passMaster();
                $_passMaster = $passMaster::first('password');
                if ( !password_verify( $postData['passMaster'], $_passMaster->password) )
                    $responseMessage = 'Ha ocurrido un error! Informe a soporte ex002';
                else{
                    //verificando si ya existe ese usuario
                    $usuarios = new usuarios();
                    $existeusuario = $usuarios
                                ->where("cedula", $postData['cedula'])
                                ->select('cedula')
                                ->first();
                    if ( $existeusuario ) {
                        $responseMessage = 'Este usuario ya esta registrado';
                    }else{
                        $usuarios->cedula = $postData['cedula'];
                        $usuarios->rol = $postData['rol'];
                        $usuarios->nombres = $postData['nombres'];
                        $usuarios->apellidos = $postData['apellidos'];
                        $usuarios->telefono = $postData['telefono'];
                        $usuarios->correo = $postData['email'];
                        $usuarios->user_name = $postData['user_name'];
                        $postData['password'] = password_hash($postData['password'], PASSWORD_DEFAULT );
                        $usuarios->password = $postData['password'];
                        $usuarios->save();
                        $responseMessage = 'Se ha guardado con éxito';
                        $_SESSION['user_name'] = $postData['user_name'];
                        $_SESSION['rol'] = $postData['rol'];
                        return new RedirectResponse('/dashboard');
                    }
                }
            } catch (\Exception $e) {
                $responseMessage = 'Ha ocurrido un error! ex001';
                // $responseMessage = $e->getMessage();
            }
            // return $this->jsonReturn($existeusuario);
        }
        $assets = new assetsControler();
        return $this->renderHTML('signup.twig', [
            'responseMessage' => $assets->alert($responseMessage, 'warning')
        ]);
    }

    public function getLogoutAction(){
        unset($_SESSION['user_name']);
        unset($_SESSION['rol']);
        session_destroy();
        return new RedirectResponse('/');
    }
}