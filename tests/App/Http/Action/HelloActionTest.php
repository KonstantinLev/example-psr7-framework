<?php
namespace Tests\App\Http\Action;

use App\Http\Action\HelloAction;
use Framework\Template\TemplateRenderer;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class HelloActionTest extends TestCase
{
    private $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new TemplateRenderer('views');
    }

    public function testGuest()
    {
        $action = new HelloAction($this->renderer);

        $request = new ServerRequest();
        $response = $action($request);

        self::assertEquals(200, $response->getStatusCode());
        self::assertContains('Hello, Guest!', $response->getBody()->getContents());
    }

    public function testLev()
    {
        $action = new HelloAction($this->renderer);

        $request = (new ServerRequest())
            ->withQueryParams(['name' => 'Lev']);

        $response = $action($request);

        self::assertContains('Hello, Lev!', $response->getBody()->getContents());
    }
}