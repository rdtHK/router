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
    private $routes = [];

    public function add(?string $method, string $path, $controller): Route
    {
        $route = new Route($method, $path, $controller);
        $this->routes[] = $route;
        return $route;
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
        foreach ($this->routes as $route) {
            $m = $route->getMethod();

            if ($m !== null && strcasecmp($m, $method) !== 0) {
                continue;
            }

            $regex = $this->compile($route);

            if (!preg_match($regex, $path, $matches)) {
                continue;
            }

            foreach ($matches as $key => $value) {
                if (!is_string($key)) {
                    unset($matches[$key]);
                }
            }

            return [$route->getController(), $matches];
        }

        return [null, []];
    }

    private function compile(Route $route): string
    {
        $regex = $route->getPath();

        foreach ($route->getParameters() as $name => $type) {
            $regex = str_replace(":$name", "(?<$name>$type)", $regex);
        }

        $regex = '{^' . $regex . '$}';
        return $regex;
    }
}
