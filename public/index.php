<?php

use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequestFactory;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

### Initialization
$request = ServerRequestFactory::fromGlobals();

### Action
$name = $request->getQueryParams()['name'] ?? 'Guest';

$response = (new HtmlResponse('Hello, ' . $name . '!'))
    ->withHeader('X-MyHeader', 'Hello World');

### Sending
//header('HTTP/1.0 ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());
header(sprintf(
    'HTTP/%s %d %s',
    $response->getProtocolVersion(),
    $response->getStatusCode(),
    $response->getReasonPhrase()
));


foreach ($response->getHeaders() as $name => $values) {
    //header($name . ':' . implode(', ', $values));
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
echo $response->getBody();