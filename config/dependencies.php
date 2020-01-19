<?php

use App\Http\Middleware;
use App\Http\Action;
use Framework\Container\Container;
use Framework\Http\Application;
use Framework\Http\Middleware\DispatchMiddleware;
use Framework\Http\Middleware\RouteMiddleware;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Router\Router;
use Zend\Diactoros\Response;

/** @var Container $container */
$container->set(Application::class, function (Container $container) {
    return new Application(
        $container->get(MiddlewareResolver::class),
        $container->get(Router::class),
        new Middleware\NotFoundHandler()
    );
});
$container->set(Router::class, function () {
    return new AuraRouterAdapter(new Aura\Router\RouterContainer());
});
$container->set(MiddlewareResolver::class, function () {
    return new MiddlewareResolver(new Response());
});
$container->set(MiddlewareResolver::class, function (Container $container) {
    return new MiddlewareResolver(new Response(), $container);
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
$container->set(RouteMiddleware::class, function (Container $container) {
    return new RouteMiddleware($container->get(Router::class));
});

$container->set(Middleware\CredentialsMiddleware::class, function () {
    return new Middleware\CredentialsMiddleware();
});

$container->set(Middleware\ProfilerMiddleware::class, function () {
    return new Middleware\ProfilerMiddleware();
});

$container->set(Action\HelloAction::class, function () {
    return new Action\HelloAction();
});

$container->set(Action\CatAction::class, function () {
    return new Action\CatAction();
});

$container->set(Action\CabinetAction::class, function () {
    return new Action\CabinetAction();
});

$container->set(Action\Blog\IndexAction::class, function () {
    return new Action\Blog\IndexAction();
});

$container->set(Action\Blog\ShowAction::class, function () {
    return new Action\Blog\ShowAction();
});