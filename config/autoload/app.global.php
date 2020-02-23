<?php

use App\Http\Middleware;
use Infrastructure\Framework\Http\Middleware\ErrorHandler\PrettyErrorResponseGenerator;
use Infrastructure\Framework\Http\Middleware\ErrorHandler\LogErrorListener;
use App\Http\Middleware\ErrorHandler\DebugErrorResponseGenerator;
use Framework\Http\Middleware\ErrorHandler\ErrorHandlerMiddleware;
use Framework\Http\Middleware\ErrorHandler\WhoopsErrorResponseGenerator;
use Framework\Http\Middleware\ErrorHandler\ErrorResponseGenerator;
use Framework\Http\Application;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Router\Router;
use Framework\Template\TemplateRenderer;
use Laminas\Diactoros\Response;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Psr\Container\ContainerInterface;

return [
    'dependencies' => [
        'abstract_factories' => [
            ReflectionBasedAbstractFactory::class,
        ],
        'factories' => [
            Application::class => function (ContainerInterface $container) {
                return new Application(
                    $container->get(MiddlewareResolver::class),
                    $container->get(Router::class),
                    $container->get(Middleware\NotFoundHandler::class)
                );
            },
            Router::class => function () {
                return new AuraRouterAdapter(new Aura\Router\RouterContainer());
            },
            MiddlewareResolver::class => function (ContainerInterface $container) {
                return new MiddlewareResolver(new Response(), $container);
            },
            ErrorHandlerMiddleware::class => function (ContainerInterface $container) {
//                return new ErrorHandlerMiddleware(
//                    $container->get(ErrorResponseGenerator::class),
//                    $container->get(Psr\Log\LoggerInterface::class)
//                );
                $middleware =  new ErrorHandlerMiddleware(
                    $container->get(ErrorResponseGenerator::class)
                );
                $middleware->addListener($container->get(LogErrorListener::class));
                return $middleware;
            },
            ErrorResponseGenerator::class => function (ContainerInterface $container) {
                return new PrettyErrorResponseGenerator(
                    $container->get(TemplateRenderer::class),
                    new Laminas\Diactoros\Response(),
                    [
                        '403' => 'error/403',
                        '404' => 'error/404',
                        'error' => 'error/error',
                    ]
                );
            },
            Psr\Log\LoggerInterface::class => function (ContainerInterface $container)
            {
                $logger = new Monolog\Logger('App');
                $logger->pushHandler(new Monolog\Handler\StreamHandler(
                    'var/log/application.log',
                    $container->get('config')['debug'] ? Monolog\Logger::DEBUG : Monolog\Logger::WARNING
                ));
                return $logger;
            },
        ],
    ],
    'debug' => true,
];
