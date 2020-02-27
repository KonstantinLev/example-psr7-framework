<?php

namespace Infrastructure\Framework\Http\Middleware\ErrorHandler;

use Framework\Template\TemplateRenderer;
use Laminas\Diactoros\Response;
use Psr\Container\ContainerInterface;

class PrettyErrorResponseGeneratorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new PrettyErrorResponseGenerator(
            $container->get(TemplateRenderer::class),
            new Response(),
            [
                '403' => 'error/403',
                '404' => 'error/404',
                'error' => 'error/error',
            ]
        );
    }
}
