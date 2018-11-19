<?php

namespace Inhere\Route\Test;

use Inhere\Route\Route;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Inhere\Route\Route
 */
class RouteTest extends TestCase
{
    public function testCreateFromArray()
    {
        $route = Route::createFromArray([
            'path' => '/kfhxlkeugug/{name}',
            'method' => 'GET',
            'handler' => 'handler_func',
            'bindVars' => [],
            'params' => [],
            'pathVars' => ['name',],
            'pathRegex' => '#^/kfhxlkeugug/([^/]+)$#',
            'pathStart' => '/kfhxlkeugug/',
            'chains' => [],
            'options' => [],
        ]);

        $this->assertEquals('GET', $route->getMethod());
        $this->assertEquals(['name'], $route->getPathVars());
        $this->assertEquals('/kfhxlkeugug/', $route->getPathStart());
        $this->assertEquals('#^/kfhxlkeugug/([^/]+)$#', $route->getPathRegex());
    }

    public function testParseParam()
    {
        // 抽象方法才需要配置
        // $stub->expects($this->any())
        //     ->method('parseParamRoute')
        //     ->will($this->returnValue('foo'));

        $path = '/im/{name}/{age}';
        $route = Route::create('GET', $path, 'my_handler');
        $first = $route->parseParam(['age' => '\d+']);
        $this->assertCount(2, $route->getPathVars());
        $this->assertEquals('im', $first);// first node
        $this->assertEquals(['name', 'age'], $route->getPathVars());
        $this->assertEquals('/im/', $route->getPathStart());
        $this->assertEquals('#^/im/([^/]+)/(\d+)$#', $route->getPathRegex());

        $path = '/path/to/{name}';
        $route = Route::create('GET', $path, 'my_handler');
        $first = $route->parseParam([]);
        $this->assertEquals('path', $first);
        $this->assertEquals('/path/to/', $route->getPathStart());

        $path = '/path/to/some/{name}';
        $route = Route::create('GET', $path, 'my_handler');
        $first = $route->parseParam([]);
        $this->assertEquals('path', $first);
        $this->assertEquals('/path/to/some/', $route->getPathStart());

        $path = '/hi/{name}';
        $route = Route::create('GET', $path, 'my_handler');
        $first = $route->parseParam([]);
        $this->assertEquals('hi', $first);
        $this->assertEquals(['name'], $route->getPathVars());
        $this->assertEquals('/hi/', $route->getPathStart());
        $this->assertEquals('#^/hi/([^/]+)$#', $route->getPathRegex());

        $path = '/hi[/{name}]';
        $route = Route::create('GET', $path, 'my_handler');
        $first = $route->parseParam([]);
        $this->assertEquals('', $first);
        $this->assertEquals(['name'], $route->getPathVars());
        $this->assertEquals('/hi', $route->getPathStart());
        $this->assertEquals('#^/hi(?:/([^/]+))?$#', $route->getPathRegex());

        $path = '/hi[/tom]';
        $route = Route::create('GET', $path, 'my_handler');
        $first = $route->parseParam([]);
        $this->assertEquals('', $first);
        $this->assertEquals([], $route->getPathVars());
        $this->assertEquals('/hi', $route->getPathStart());
        $this->assertEquals('#^/hi(?:/tom)?$#', $route->getPathRegex());

        $path = '/hi/[tom]';
        $route = Route::create('GET', $path, 'my_handler');
        $first = $route->parseParam([]);
        $this->assertEquals('hi', $first);
        $this->assertEquals('/hi/', $route->getPathStart());
        $this->assertEquals('#^/hi/(?:tom)?$#', $route->getPathRegex());

        $path = '/{category}';
        $route = Route::create('GET', $path, 'my_handler');
        $first = $route->parseParam();
        $this->assertEquals('', $first);
        $this->assertEquals('', $route->getPathStart());
        $this->assertEquals('#^/([^/]+)$#', $route->getPathRegex());

        $path = '/blog-{category}';
        $route = Route::create('GET', $path, 'my_handler', ['category' => '\w+']);
        $first = $route->parseParam();
        $this->assertEquals('', $first);
        $this->assertEquals('/blog-', $route->getPathStart());
        $this->assertEquals('#^/blog-(\w+)$#', $route->getPathRegex());
    }
}