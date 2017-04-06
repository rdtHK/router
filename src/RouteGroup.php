<?php

declare(strict_types=1);

/**
 * Copyright 2017 Mário Camargo Palmeira
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
 *
 */
class RouteGroup extends Route
{
    private $path;
    private $children;

    public function __construct(string $path)
    {
        parent::__construct($path);
        $this->children = [];
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function group(string $path, callable $callback): Route
    {
        $route = new RouteGroup($path);
        $callback($route);
        $this->children[] = $route;
        return $route;
    }

    public function add(?string $method, string $path, $controller): Route
    {
        $route = new ControllerRoute($method, $path, $controller);
        $this->children[] = $route;
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

}
