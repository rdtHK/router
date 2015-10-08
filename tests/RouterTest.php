<?php

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

class RouterTest extends PHPUnit_Framework_TestCase
{
    private $router;

    public function setUp()
    {
        $this->router = new Router();
        $this->router->set('/', 'only_slash');
        $this->router->set('/abc/', 'static_route');
        $this->router->set('{foo}', 'only_param');
        $this->router->set('/{foo}', 'slash_param');
        $this->router->set('{foo}/', 'param_slash');
        $this->router->set('/{foo}/', 'slash_param_slash');
        $this->router->set('-{foo}', 'dash_param');
        $this->router->set('{foo}-', 'param_dash');
        $this->router->set('-{foo}-', 'dash_param_dash');
        $this->router->set('abc{foo}uvw', 'text_param_text');
        $this->router->set('{foo}-{bar}', 'param_dash_param');
        $this->router->set('{foo}/{bar}', 'param_slash_param');
        $this->router->set('/{foo}/{bar}/', 'slash_param_slash_param');
        $this->router->set('/abc/{foo}/xyz/{bar}/uvw', 'text_param_text_param_text');
    }

    public function testStaticRoutes()
    {
        list($controller, $params) = $this->router->run('/');
        $this->assertEquals($controller, 'only_slash');
        $this->assertEmpty($params);

        list($controller, $params) = $this->router->run('/abc/');
        $this->assertEquals($controller, 'static_route');
        $this->assertEmpty($params);
    }

    public function testSingleParamRoutes()
    {
        $pathControllers = [
            'bar' => 'only_param',
            '/bar' => 'slash_param',
            'bar/' => 'param_slash',
            '/bar/' => 'slash_param_slash',
            '-bar' => 'dash_param',
            'bar-' => 'param_dash',
            '-bar-' => 'dash_param_dash',
            'abcbaruvw' => 'text_param_text',
        ];
        foreach ($pathControllers as list($path, $controller)) {
            list($controller, $params) = $this->router->run($path);
            $this->assertEquals($controller, $controller);
            $this->assertCount($params, 1);
            $this->assertEquals($params, ['foo' => 'bar']);
        }
    }

    public function testMultipleParamRoutes()
    {
        $pathControllers = [
            'foo-bar' => 'param_dash_param',
            'foo/bar' => 'param_slash_param',
            '/foo/bar/' => 'slash_param_slash_param',
            '/abc/foo/xyz/bar/uvw' => 'text_param_text_param_text',
        ];

        foreach ($pathControllers as list($path, $controller)) {
            list($controller, $params) = $this->router->run($path);
            $this->assertEquals($controller, $controller);
            $this->assertCount($params, 1);
            $this->assertEquals($params, ['foo' => 'bar']);
        }
    }

    public function testRedundantRules()
    {
        $router = new Router();
        $router->set('{foo}{bar}', 'param_param');
        $router->set('{foo}', 'param');
        list($controller, $params) = $this->router->run('abc');
        $this->assertEquals($controller, 'param_param');
        $this->assertEquals($params, ['foo' => 'a', 'bar' => 'bc']);

        $router = new Router();
        $router->set('{foo}', 'param');
        $router->set('{foo}{bar}', 'param_param');
        list($controller, $params) = $this->router->run('abc');
        $this->assertEquals($controller, 'param');
        $this->assertEquals($params, ['foo' => 'abc']);
    }

    /**
     * Ensures the router will throw an
     * exception when the user tries to
     * match a null path.
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage  'NULL' is not a string.
     */
    public function testNullPath()
    {
        $this->router->run(null);
    }

    /**
     * Ensures the router will throw an
     * exception when the user tries to
     * match a non-string path.
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage  'integer' is not a string.
     */
    public function testInvalidPath()
    {
        $this->router->run(1);
    }

    /**
     * Ensures the router will throw an
     * exception when the user tries to
     * add a null route.
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage  'NULL' is not a string.
     */
    public function testNullRoute()
    {
        $this->router->add(null);
    }

    /**
     * Ensures the router will throw an
     * exception when the user tries to
     * add a non-string route.
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage  'integer' is not a string.
     */
    public function testInvalidRoute()
    {
        $this->router->add(1);
    }
}
