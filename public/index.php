<?php

use Framework\Http\ActionResolver;
use Framework\Http\Router\Exception\RequestNotMatchedException;
use Framework\Http\Router\AuraRouterAdapter;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use App\Http\Action;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

### Initialization

$config = [
    'users' => ['admin' => 'password'],
];

$aura = new Aura\Router\RouterContainer();
$routes = $aura->getMap();

$routes->get('home', '/', Action\HelloAction::class);
$routes->get('cat', '/cat', Action\CatAction::class);
$routes->get('cabinet', '/cabinet', new Action\BasicAuthActionDecorator(
    new Action\CabinetAction(),
    $config['users']
));
$routes->get('blog', '/blog', Action\Blog\IndexAction::class);
$routes->get('blog.show', '/blog/{id}', Action\Blog\ShowAction::class)->tokens(['id' => '\d+']);

$router = new AuraRouterAdapter($aura);
$resolver = new ActionResolver();

### Running
$request = ServerRequestFactory::fromGlobals();

try {
    $result = $router->match($request);
    foreach ($result->getAttributes() as $attribute => $value) {
        $request = $request->withAttribute($attribute, $value);
    }
    $handler = $result->getHandler();
    /** @var callable $action */
    $action = $resolver->resolve($result->getHandler());
    $response = $action($request);
} catch (RequestNotMatchedException $e){
    $response = new HtmlResponse('Undefined page', 404);
}

### Postprocessing
$response = $response->withHeader('X-MyHeader', 'Hello World');

### Sending

$emitter = new SapiEmitter();
$emitter->emit($response);