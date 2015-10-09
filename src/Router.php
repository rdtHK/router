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

namespace Rdthk\Routing;

/**
 * Small dependency injection container that can also double as
 * a service locator. Or maybe the opposite.
 */
class Router
{
    private $routes;

    function __construct() {
        $this->routes = [];
    }

    public function add($route, $controller) {
        $type = gettype($route);
        if ($type !== 'string') {
            throw new \InvalidArgumentException(
                "'$type' is not a string."
            );
        }
        $this->routes[] = [$this->compile($route), $controller];
        return $this;
    }

    public function run($path) {
        $type = gettype($path);
        if ($type !== 'string') {
            throw new \InvalidArgumentException(
                "'$type' is not a string."
            );
        }
        foreach ($this->routes as list($compiledRoute, $controller)) {
            $params = $this->match($compiledRoute, $path);

            if ($params !== false) {
                return [$controller, $params];
            }
        }
        return [null, []];
    }

    public function compile($route)
    {
        $compiled = [];
        $begin = 0;
        $state = 'str';
        for ($i = 0; $i < strlen($route); $i++) {
            if ($state === 'param' && $route[$i] === '{') {
                // Syntax Error
                throw new \InvalidArgumentException(
                    "Nested parameters are not allowed."
                );
            }
            if ($state === 'str' && $route[$i] === '}') {
                // Syntax Error
                throw new \InvalidArgumentException(
                    "Trying to close unopenend bracket."
                );
            }
            if (
                ($state === 'param' && $route[$i] === '}') ||
                ($state === 'str' && $route[$i] === '{')
            ) {
                $str = substr($route, $begin, $i - $begin);
                $begin = $i + 1;
                $compiled[] = [$state, $str];
                $state = $state === 'str'? 'param':'str';
            }
        }
        if ($state === 'param' && $route[strlen($route) - 1] !== '}') {
            // Syntax Error
            throw new \InvalidArgumentException(
                "Missing closing bracket."
            );
        }
        if ($state === 'str') {
            $compiled[] = ['str', substr($route, $begin)];
        }
        return $compiled;
    }

    public function match($compiled, $path) {
        return false;
    }
}
