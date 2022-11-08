<?php

namespace Pingframework\Web\Tests\Http;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Pingframework\Web\Http\HttpRequestHandler;
use Pingframework\Web\Tests\TestApplication;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UriFactory;
use Slim\Psr7\UploadedFile;

class HttpRequestHandlerTest extends TestCase
{
    private TestApplication $app;

    protected function setUp(): void
    {
        $this->app = TestApplication::build();
    }

    public function testHandle()
    {
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            new ServerRequest('GET', '/test/test/1', [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
    }

    public function testHandleRequestBody()
    {
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            new ServerRequest('POST', '/test/test2', [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ], '{"foo":"bar"}')
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"foo":"bar"}', (string)$response->getBody());
    }

    public function testHandleRequestBodyJson()
    {
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            new ServerRequest('POST', '/test/test3', [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ], '{"foo":"bar"}')
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"foo":"bar"}', (string)$response->getBody());
    }

    public function testHandleRequestBodySchema()
    {
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            new ServerRequest('POST', '/test/test4', [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ], '{"foo":"bar"}')
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"foo":"bar"}', (string)$response->getBody());
    }

    public function testHandleRequestBodySchemaList()
    {
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            new ServerRequest('POST', '/test/test5', [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ], '[{"foo":"bar"}]')
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('[{"foo":"bar"}]', (string)$response->getBody());
    }

    public function testHandleQueryParams()
    {
        $uriFactory = new UriFactory();
        $requestFactory = new ServerRequestFactory(
            new StreamFactory(),
            $uriFactory
        );

        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            $requestFactory->createServerRequest('GET', '/test/test6?foo=bar')
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"foo":"bar"}', (string)$response->getBody());
    }

    public function testHandleQueryParam()
    {
        $uriFactory = new UriFactory();
        $requestFactory = new ServerRequestFactory(
            new StreamFactory(),
            $uriFactory
        );

        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            $requestFactory->createServerRequest('GET', '/test/test7?foo=bar')
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('bar', (string)$response->getBody());
    }

    public function testHandlePostParams()
    {
        $uriFactory = new UriFactory();
        $streamFactory = new StreamFactory();
        $requestFactory = new ServerRequestFactory(
            $streamFactory,
            $uriFactory
        );

        /** @var HttpRequestHandler $handler */
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $request = $requestFactory->createServerRequest('POST', '/test/test8');
        $request = $request->withBody($streamFactory->createStream('foo=bar'));
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"foo":"bar"}', (string)$response->getBody());
    }

    public function testHandlePostParam()
    {
        $uriFactory = new UriFactory();
        $streamFactory = new StreamFactory();
        $requestFactory = new ServerRequestFactory(
            $streamFactory,
            $uriFactory
        );

        /** @var HttpRequestHandler $handler */
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $request = $requestFactory->createServerRequest('POST', '/test/test9');
        $request = $request->withBody($streamFactory->createStream('foo=bar'));
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('bar', (string)$response->getBody());
    }

    public function testHandleError()
    {
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            new ServerRequest('POST', '/test/test/foo', [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ], '[{"foo":"bar"}]')
        );

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGlobalMiddleware()
    {
        /** @var HttpRequestHandler $handler */
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            new ServerRequest('GET', '/test/test/1', [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
        $this->assertEquals('Test', $response->getHeaderLine('X-Test-Header'));
        $this->assertEquals('', $response->getHeaderLine('X-Test-Group-Header'));
        $this->assertEquals('', $response->getHeaderLine('X-Test-Route-Header'));
    }

    public function testGroupMiddleware()
    {
        /** @var HttpRequestHandler $handler */
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            new ServerRequest('GET', '/test-group/test/1', [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
        $this->assertEquals('Test', $response->getHeaderLine('X-Test-Header'));
        $this->assertEquals('Group', $response->getHeaderLine('X-Test-Group-Header'));
        $this->assertEquals('', $response->getHeaderLine('X-Test-Route-Header'));
    }

    public function testRouteMiddleware()
    {
        /** @var HttpRequestHandler $handler */
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            new ServerRequest('GET', '/test-route/1', [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, (string)$response->getBody());
        $this->assertEquals('Test', $response->getHeaderLine('X-Test-Header'));
        $this->assertEquals('', $response->getHeaderLine('X-Test-Group-Header'));
        $this->assertEquals('Route', $response->getHeaderLine('X-Test-Route-Header'));
    }

    public function testUploadFile()
    {
        /** @var HttpRequestHandler $handler */
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $request = new ServerRequest('POST', '/upload', [
            'Content-Type' => 'multipart/form-data',
        ]);
        $file = __DIR__ . '/../config.php';
        $request = $request->withUploadedFiles([
            'file1' => new UploadedFile($file, 'file1', 'text/plain', filesize($file), UPLOAD_ERR_OK),
        ]);
        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(file_get_contents($file), (string)$response->getBody());
    }

    public function testHeaders()
    {
        /** @var HttpRequestHandler $handler */
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            new ServerRequest('GET', '/test/test10', [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            '{"Content-Type":["application\/json"],"Accept":["application\/json"]}',
            (string)$response->getBody()
        );
    }

    public function testHeader()
    {
        /** @var HttpRequestHandler $handler */
        $handler = $this->app->getApplicationContext()->get(HttpRequestHandler::class);
        $response = $handler->handle(
            new ServerRequest('GET', '/test/test11', [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'X-Foo'        => 'bar',
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('bar', (string)$response->getBody());
    }
}
