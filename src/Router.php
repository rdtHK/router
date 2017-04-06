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

namespace Rdthk\Routing;

/**
 * Small router class.
 * It does not have any fancy type checking on the patterns
 * and also does not make any assumptions about what kind of 'controller'
 * is passed to it.
 *
 * Please note that the param matching is non-greedy.
 *
 * Usage:
 *
 * $router = new Router();
 * $router->add('get', /articles/:id-name', 'print_article')->param('id', '\d+');
 * list($controller, $params) = $router->run('/articles/123-article-title');
 *
 * echo $controller; // 'print_article'
 * echo $params; // ['id' => '123', 'name' => 'article-title']
 *
 */
class Router
{
    private $routes;

    public function __construct()
    {
        $this->routes = new RouteGroup('');
    }

    public function group(string $path, callable $callback): Route
    {
        return $this->routes->group($path, $callback);
    }

    public function add(?string $method, string $path, $controller): Route
    {
        return $this->routes->add($method, $path, $controller);
    }

    public function get(string $path, $controller): Route
    {
        return $this->add('get', $path, $controller);
    }

    public function post(string $path, $controller): Route
    {
        return $this->add('post', $path, $controller);
    }

    public function put(string $path, $controller): Route
    {
        return $this->add('put', $path, $controller);
    }

    public function delete(string $path, $controller): Route
    {
        return $this->add('delete', $path, $controller);
    }

    /**
     * Tries to match the path with the available patterns.
     *
     * This method will always return an array with two elements:
     * a controller followed by a list of params extracted from the path.
     *
     * If the pattern couldn't be matched, the first element of the return
     * value will be null and the second an empty array.
     */
    public function run(string $method, string $path): array
    {
        return $this->runRouteGroup($this->routes, $method, $path, '', []);
    }

    private function runRouteGroup(RouteGroup $group, string $reqMethod,
        string $reqPath, string $path, array $params)
    {
        $path = $path . $group->getPath();
        $params = array_merge($params, $group->getParameters());

        foreach ($group->getChildren() as $child) {
            if ($child instanceof RouteGroup) {
                $result = $this->runRouteGroup(
                    $child,
                    $reqMethod,
                    $reqPath,
                    $path,
                    $params
                );
            } else {
                $result = $this->runControllerRoute(
                    $child,
                    $reqMethod,
                    $reqPath,
                    $path,
                    $params
                );
            }

            if ($result[0] !== null) {
                return $result;
            }
        }

        return [null, []];
    }

    private function runControllerRoute(ControllerRoute $route, string $reqMethod,
        string $reqPath, string $path, array $params)
    {
        $path = $path . $route->getPath();
        $params = array_merge($params, $route->getParameters());
        $method = $route->getMethod();

        if ($method !== null && strcasecmp($method, $reqMethod) !== 0) {
            return [null, []];
        }

        $regex = $this->compile($path, $params);

        if (!preg_match($regex, $reqPath, $matches)) {
            return [null, []];
        }

        foreach ($matches as $key => $value) {
            if (!is_string($key)) {
                unset($matches[$key]);
            }
        }

        return [$route->getController(), $matches];
    }

    private function compile(string $path, array $params): string
    {
        $regex = $path;

        foreach ($params as $name => $type) {
            $regex = str_replace(":$name", "(?<$name>$type)", $regex);
        }

        $regex = '{^' . $regex . '$}';
        return $regex;
    }
}
