<?php

declare(strict_types=1);

use FastD\Http\Request\ServerRequest;
use PHPUnit\Framework\TestCase;

final class ServerRequestBodyTest extends TestCase
{
    private array $server = [];
    private array $post = [];
    private array $get = [];
    private array $cookie = [];
    private array $files = [];

    protected function setUp(): void
    {
        $this->server = $_SERVER;
        $this->post = $_POST;
        $this->get = $_GET;
        $this->cookie = $_COOKIE;
        $this->files = $_FILES;

        $_SERVER = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/api/users',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
            'HTTP_HOST' => 'localhost',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ];
        $_POST = [];
        $_GET = [];
        $_COOKIE = [];
        $_FILES = [];
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
        $_POST = $this->post;
        $_GET = $this->get;
        $_COOKIE = $this->cookie;
        $_FILES = $this->files;
    }

    public function testJsonPostBodyIsParsedAsPhpArray(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $body = '{"name":"FastD","tags":["api","swoole"],"meta":{"version":8}}';
        $request = ServerRequest::fromGlobals($body);

        $this->assertSame([
            'name' => 'FastD',
            'tags' => ['api', 'swoole'],
            'meta' => ['version' => 8],
        ], $request->getParsedBody());
        $this->assertSame($body, (string)$request->getBody());
    }

    public function testJsonContentTypeWithCharsetIsParsedAsPhpArray(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json; charset=utf-8';

        $request = ServerRequest::fromGlobals('{"page":2}');

        $this->assertSame(['page' => 2], $request->getParsedBody());
    }

    public function testVendorJsonContentTypeIsParsedAsPhpArray(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'application/problem+json';

        $request = ServerRequest::fromGlobals('{"title":"Invalid request"}');

        $this->assertSame(['title' => 'Invalid request'], $request->getParsedBody());
    }

    public function testInvalidJsonBodyFallsBackToEmptyArray(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $request = ServerRequest::fromGlobals('{not json');

        $this->assertSame([], $request->getParsedBody());
    }

    public function testJsonPutBodyIsParsedAsPhpArray(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $request = ServerRequest::fromGlobals('{"name":"updated"}');

        $this->assertSame(['name' => 'updated'], $request->getParsedBody());
    }

    public function testNonJsonPostBodyKeepsPhpPostValues(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $_POST = ['name' => 'form'];

        $request = ServerRequest::fromGlobals('name=raw');

        $this->assertSame(['name' => 'form'], $request->getParsedBody());
    }
}
