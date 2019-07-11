<?php 

require __DIR__ . '/../vendor/autoload.php';



$app = new Slim\App([
    'settings' => [
    'displayErrorDetails' => true, // set to false in production
    'addContentLengthHeader' => false, // Allow the web server to send the content-length header

    'db' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'projectocrud',
        'username' => 'root',
        'password' => '',
    ],
]
]);

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};

$container['view'] = function ($c) {
   $view = new \Slim\Views\Twig(__DIR__. '/../resources/views', [
       'cache' =>false, 
       ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
    $c->router,
    $c->request->geturi()
));
return $view;
};

$container['validator'] = function($c){

    return new App\Validation\Validator;
};

$container['HomeController'] = function($c){
    $view = $c->get("view");
    return new \App\Controllers\HomeController($c);
};

$container['AuthController'] = function($c){
    $view = $c->get("view");
    return new \App\Controllers\Auth\AuthController($c);
};

$container['csrf'] = function($c){
    return new \Slim\Csrf\Guard;
};

$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));

$app->add($container->csrf);

v::with('App\\Validation\\Rules\\');

require __DIR__ . '/../app/routes.php';