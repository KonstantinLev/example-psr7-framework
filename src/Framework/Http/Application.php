<?php

namespace Framework\Http;

use Framework\Http\Pipeline\MiddlewareResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Zend\Stratigility\MiddlewarePipe;

class Application implements MiddlewareInterface, RequestHandlerInterface
{
    private $resolver;
    private $default;
    private $pipeline;

    public function __construct(MiddlewareResolver $resolver, RequestHandlerInterface $default)
    {
        $this->resolver = $resolver;
        $this->pipeline = new MiddlewarePipe();
        $this->default = $default;
    }

    public function pipe($path, $middleware = null): void
    {
        if ($middleware === null) {
            $this->pipeline->pipe($this->resolver->resolve($path));
        }
        $this->pipeline->pipe($this->resolver->resolve($path));
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->pipeline->process($request, $this->default);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->pipeline->process($request, $handler);
    }
}