<?php

declare(strict_types=1);

/**
 * Copyright 2015 Mário Camargo Palmeira
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
use Rdthk\Routing\Router;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testSimpleRouteMatching()
    {
        $router = new Router;
        $router->get('/foo/', 'controller');
        $this->assertEquals(['controller', []], $router->run('get', '/foo/'));
    }

    public function testMethodMatchingIsCaseInsensitive()
    {
        $router = new Router;
        $router->add('GET', '/foo/', 'controller');
        $this->assertEquals(['controller', []], $router->run('get', '/foo/'));
    }

    public function testSingleParamRoute()
    {
        $router = new Router;
        $router->get('/:foo/', 'controller');
        list($controller, $params) = $router->run('get', '/bar/');
        $this->assertEquals('controller', $controller);
        $this->assertEquals(['foo' => 'bar'], $params);
    }

    public function testMultipleParameterRoutes()
    {
        $router = new Router;
        $router->get('/:foo/:bar/', 'controller');
        list($controller, $params) = $router->run('get', '/1/2/');
        $this->assertEquals('controller', $controller);
        $this->assertEquals(['foo' => '1', 'bar' => '2'], $params);
    }

    public function testMultipleRoutes()
    {
        $router = new Router;
        $router->get('/foo', 'c1');
        $router->get('/bar', 'c2');
        $this->assertEquals(['c2', []], $router->run('get', '/bar'));
    }

    public function testNoMatches()
    {
        $router = new Router;
        $router->get('/foo', 'controller');
        $this->assertEquals([null, []], $router->run('get', '/bar'));
    }

    public function testRouteGroups()
    {
        $router = new Router;
        $router->group('/foo', function ($router) {
            $router->get('/bar', 'controller');
        });
        $this->assertEquals(
            ['controller', []],
            $router->run('get', '/foo/bar')
        );
    }

    public function testRouteGroupParameters()
    {
        $router = new Router;
        $router->group('/foo/:id', function ($router) {
            $router->get('/bar', 'controller');
        });
        $this->assertEquals(
            ['controller', ['id' => 'baz']],
            $router->run('get', '/foo/baz/bar')
        );
    }
}
