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
    public function testStaticRoutes()
    {
        $router = new Router();
        $router->add('/', 'only_slash');
        $router->add('/abc/', 'static_route');

        list($controller, $params) = $router->run('/');
        $this->assertEquals($controller, 'only_slash');
        $this->assertEmpty($params);

        list($controller, $params) = $router->run('/abc/');
        $this->assertEquals($controller, 'static_route');
        $this->assertEmpty($params);
    }

    public function testSingleParamRoutes()
    {
        $cases = [
            ['{foo}', 'bar', 'only_param'],
            ['/{foo}', '/bar', 'slash_param'],
            ['{foo}/', 'bar/', 'param_slash'],
            ['/{foo}/', '/bar/', 'slash_param_slash'],
            ['-{foo}', '-bar', 'dash_param'],
            ['{foo}-', 'bar-', 'param_dash'],
            ['-{foo}-', '-bar-', 'dash_param_dash'],
            ['abc{foo}uvw', 'abcbaruvw', 'text_param_text'],
        ];
        foreach ($cases as list($pattern, $path, $c)) {
            $router = new Router();
            $router->add($pattern, $c);
            list($controller, $params) = $router->run($path);
            $this->assertEquals($controller, $c);
            $this->assertCount($params, 1);
            $this->assertEquals($params, ['foo' => 'bar']);
        }
    }

    public function testMultipleParamRoutes()
    {
        $cases = [
            ['{foo}-{bar}', 'bar-foo', 'param_dash_param'],
            ['{foo}/{bar}', 'bar/foo', 'param_slash_param'],
            ['/{foo}/{bar}/', '/bar/foo/', 'slash_param_slash_param'],
            [
                '/abc/{foo}/xyz/{bar}/uvw',
                '/abc/bar/xyz/foo/uvw',
                'text_param_text_param_text'
            ],
        ];

        foreach ($cases as list($pattern, $path, $c)) {
            $router = new Router();
            $router->add($pattern, $c);
            list($controller, $params) = $router->run($path);
            $this->assertEquals($controller, $c);
            $this->assertCount($params, 2);
            $this->assertEquals($params, [
                'foo' => 'bar',
                'bar' => 'foo'
            ]);
        }
    }

    public function testRedundantRules()
    {
        $router = new Router();
        $router->add('{foo}{bar}', 'param_param');
        $router->add('{foo}', 'param');
        list($controller, $params) = $router->run('abc');
        $this->assertEquals($controller, 'param_param');
        $this->assertEquals($params, ['foo' => '', 'bar' => 'abc']);

        $router = new Router();
        $router->add('{foo}', 'param');
        $router->add('{foo}{bar}', 'param_param');
        list($controller, $params) = $router->run('abc');
        $this->assertEquals($controller, 'param');
        $this->assertEquals($params, ['foo' => 'abc']);
    }

    public function testNoMatches()
    {
        $router = new Router();
        $router->add('/', 'foo');
        list($controller, $params) = $router->run('bar');
        $this->assertNull($controller);
        $this->assertEmpty($params);
    }

    public function testAddReturnsThis()
    {
        $router = new Router();
        $x = $router->add('foo', 'bar');
        $this->assertSame($router, $x);
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage  Missing closing bracket.
     */
    public function testUnmatchedBracketInTheEnd()
    {
        $router = new Router();
        $router->add('{test', '');
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage  Missing closing bracket.
     */
    public function testUnmatchedBracketInTheMiddle()
    {
        $router = new Router();
        $router->add('/{test/', '');
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage  Nested parameters are not allowed.
     */
    public function testNestedBrackets()
    {
        $router = new Router();
        $router->add('/{{test}}/', '');
    }


    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage  'integer' is not a string.
     */
    public function testInvalidPath()
    {
        $router = new Router();
        $router->run(1);
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage  'integer' is not a string.
     */
    public function testInvalidRoute()
    {
        $router = new Router();
        $router->add(1, '');
    }
}
