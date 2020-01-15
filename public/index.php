<?php

use Framework\Container\Container;
use Framework\Http\Middleware\DispatchMiddleware;
use Framework\Http\Middleware\RouteMiddleware;
use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Application;
use Framework\Http\Router\Router;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use App\Http\Action;
use App\Http\Middleware;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

### Configuration

$container = new Container();
$container->set('config', [
    'debug' => true,
    'users' => ['admin' => 'password'],
]);

$container->set(Application::class, function (Container $container) {
    return new Application(
        $container->get(MiddlewareResolver::class),
        new Middleware\NotFoundHandler()
    );
});

$container->set(Middleware\BasicAuthMiddleware::class, function (Container $container) {
    return new Middleware\BasicAuthMiddleware($container->get('config')['users'], new Response());
});
$container->set(Middleware\ErrorHandlerMiddleware::class, function (Container $container) {
    return new Middleware\ErrorHandlerMiddleware($container->get('config')['debug']);
});

$container->set(DispatchMiddleware::class, function (Container $container) {
    return new DispatchMiddleware($container->get(MiddlewareResolver::class));
});

$container->set(MiddlewareResolver::class, function () {
    return new MiddlewareResolver(new Response());
});

$container->set(RouteMiddleware::class, function (Container $container) {
    return new RouteMiddleware($container->get(Router::class));
});

$container->set(Router::class, function () {
    $aura = new Aura\Router\RouterContainer();
    $routes = $aura->getMap();
    $routes->get('home', '/', Action\HelloAction::class);
    $routes->get('about', '/cat', Action\CatAction::class);
    $routes->get('cabinet', '/cabinet', Action\CabinetAction::class);
    $routes->get('blog', '/blog', Action\Blog\IndexAction::class);
    $routes->get('blog_show', '/blog/{id}', Action\Blog\ShowAction::class)->tokens(['id' => '\d+']);
    return new AuraRouterAdapter($aura);
});

### Initialization

/** @var Application $app */
$app = $container->get(Application::class);

$app->pipe($container->get(Middleware\ErrorHandlerMiddleware::class));
$app->pipe(Middleware\ProfilerMiddleware::class);
$app->pipe(Middleware\CredentialsMiddleware::class);
//$app->pipe(new Framework\Http\Middleware\RouteMiddleware($router));
$app->pipe($container->get(Framework\Http\Middleware\RouteMiddleware::class));
$app->pipe('cabinet', $container->get(Middleware\BasicAuthMiddleware::class));
//$app->pipe(new Framework\Http\Middleware\DispatchMiddleware($resolver));
$app->pipe($container->get(Framework\Http\Middleware\DispatchMiddleware::class));

### Running
$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);

### Sending

$emitter = new SapiEmitter();
$emitter->emit($response);