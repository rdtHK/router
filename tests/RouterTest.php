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
use Rdthk\Router\Router;

class RouterTest extends PHPUnit_Framework_TestCase
{
    private $router;

    public function setUp()
    {
        $this->router = new Router();
        $this->router->set('/', 'only_slash');
        $this->router->set('/abc/', 'static_route');
        $this->router->set('{foo}', 'only_param');
        $this->router->set('/{foo}', 'slash_then_param');
        $this->router->set('{foo}/', 'param_then_slash');
        $this->router->set('/{foo}/', 'slash_param_slash');
        $this->router->set('-{foo}', 'dash_param');
        $this->router->set('{foo}-', 'param_dash');
        $this->router->set('-{foo}-', 'dash_param_dash');
        $this->router->set('{foo}{bar}', 'param_param');
        $this->router->set('{foo}-{bar}', 'param_dash_param');
        $this->router->set('{foo}/{bar}', 'param_slash_param');
        $this->router->set('/{foo}/{bar}/', 'slash_param_slash_param');
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
        // TODO: Add assertions.
    }

    public function testMultipleParamRoutes()
    {
        // TODO: Add assertions.
    }

    /**
     * Ensures the router will throw an
     * exception when the user tries to
     * match a null path.
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage  Paths can only be strings. 'NULL' provided.
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
     * @expectedExceptionMessage  Paths can only be strings. 'integer' provided.
     */
    public function testInvalidPath()
    {
        $this->router->run(1);
    }
}
