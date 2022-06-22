<?php
namespace App\Controllers;

use App\Models\passMaster;
use Respect\Validation\Validator as v;

class passMasterController extends CoreController{
    public function getFormPassMasterAction(){
        return $this->renderHTML('passMaster.twig');
    }

    public function postPassMasterAction($request){
        $responseMessage = null;    //var para recuperar los mesajes q suceda durante la ejecucion

        if ($request->getMethod() == "POST") {
            $postData = $request->getParsedBody();

            $passMasterValidator = v::key('passMaster', v::stringType()->noWhitespace()->notEmpty());
            
            try {
                $passMasterValidator->assert($postData);

                $passMaster = new passMaster();
                $passMaster->truncate();       //? primero vacio la tabla pa poner una nueva contraseña master
                $postData['passMaster'] = password_hash($postData['passMaster'], PASSWORD_DEFAULT );
                $passMaster->password = $postData['passMaster'];
                $passMaster->save();
                $responseMessage = 'Se ha guardado con éxito';
            } catch (\Exception $e) {
                // $responseMessage = $e->getMessage();
                $responseMessage = 'Ha ocurrido un error! Informe a soporte';
            }
        }

        return $this->renderHTML('passMaster.twig', [
            'responseMessage' => $responseMessage
        ]);
    }
}