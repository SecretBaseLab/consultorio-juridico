<?php
// ?pa activar la visualizacion de errores en caso el servidor tenga desactivado
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

//? aqui empieza todo
require_once "../vendor/autoload.php";
session_start();

//usando vars de entorno pa acceder a la config de la base
//? estas dos lineas no necesita un servidor si ya tiene configurado en su sistema las variables de entorno como heroku
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

//? conexion con la base de datos con eloquent
use Illuminate\Database\Capsule\Manager as Capsule;
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => $_ENV['DB_DRIVER'],
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_NAME'],
    'username'  => $_ENV['DB_USER'],
    'password'  => $_ENV['DB_PASS'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();
// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

//? manejador del request/peticiones
$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

//? router
$dir_raiz = '/';   //? ruta riaz del proyecto
use Aura\Router\RouterContainer;
use Laminas\Diactoros\Response\RedirectResponse;

$routerContainer = new RouterContainer();
$map = $routerContainer->getMap();      //? generador del mapa de rutas

//rutas
$map->get('index', $dir_raiz, [
    "controller" => "App\Controllers\IndexController",
    "action" => "indexAction"
]);
$map->post('Login', $dir_raiz . 'login', [
    "controller" => "App\Controllers\loginController",
    "action" => "loginAction"
]);
$map->get('logout', $dir_raiz . 'logout', [
    "controller" => "App\Controllers\loginController",
    "action" => "getLogoutAction",
    "auth" => true
]);
$map->get('getSignUp', $dir_raiz . 'signup', [
    "controller" => "App\Controllers\loginController",
    "action" => "getSignUpAction"
]);
$map->post('postSignUp', $dir_raiz . 'signup', [
    "controller" => "App\Controllers\loginController",
    "action" => "postSignUpAction"
]);
$map->get('getFormPassmaster', $dir_raiz . 'password-master', [
    "controller" => "App\Controllers\passMasterController",
    "action" => "getFormPassMasterAction"
]);
$map->post('postPassmaster', $dir_raiz . 'password-master', [
    "controller" => "App\Controllers\passMasterController",
    "action" => "postPassMasterAction"
]);


$matcher = $routerContainer->getMatcher();
$route = $matcher->match($request);

if (!$route) {
    // get the first of the best-available non-matched routes
    $failedRoute = $matcher->getFailedRoute();

    // which matching rule failed?
    switch ($failedRoute->failedRule) {
        case 'Aura\Router\Rule\Allows':
            // 405 METHOD NOT ALLOWED
            // Send the $failedRoute->allows as 'Allow:'
            echo '405 no permitido';
            break;
        case 'Aura\Router\Rule\Accepts':
            // 406 NOT ACCEPTABLE
            echo '406 no se acepta';
            break;
        default:
            // 404 NOT FOUND
            echo '404 no se encuentra lo q buscas';
            break;
    }
} else {
    // add route attributes to the request
    foreach ($route->attributes as $key => $val) {
        $request = $request->withAttribute($key, $val);
    }
    // print_r( $route->handler);
    //recibe el array con el namesapce y la accion/metodo de esa clase
    $handlerData = $route->handler;
    $controllerName = $handlerData['controller'];
    $actionName = $handlerData['action'];
    $needsAuth = $handlerData['auth'] ?? false;

    //autenticacion
    $sessionUserId = $_SESSION['userId'] ?? null;
    if ($needsAuth && !$sessionUserId) {    //? niega el acceso si no esta logeado
        // echo 'protected route';
        $response = new RedirectResponse('/');                            
    }else{
        $controller = new $controllerName;      //genera una instancia de esa clase
        $response = $controller->$actionName($request);             //llama al metodo y recibe un obj response
    }

    //imprimendo los headers del response
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }

    http_response_code($response->getStatusCode());   //asignando el status code al response
    echo $response->getBody();  //obtiene el cuerpo html del response
}