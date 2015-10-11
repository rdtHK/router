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
    const T_PARAM = 0;
    const T_STR = 1;

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
        $state = Router::T_STR;
        $acc = '';
        for ($i = 0; $i < strlen($route); $i++) {
            $c = $route[$i];
            if ($state === Router::T_STR && $c === '{') {
                $state = Router::T_PARAM;
                if (!empty($acc)) {
                    $compiled[] = [Router::T_STR, $acc];
                    $acc = '';
                }
            } elseif ($state === Router::T_STR && $c === '}') {
                throw new \InvalidArgumentException(
                    "Trying to close unopenend bracket."
                );
            } elseif ($state === Router::T_STR) {
                $acc .= $c;
            } elseif ($state === Router::T_PARAM && $c === '}') {
                $state = Router::T_STR;
                if (empty($acc)) {
                    throw new \InvalidArgumentException(
                        "Unnamed parameters are not allowed."
                    );
                }
                $compiled[] = [Router::T_PARAM, $acc];
                $acc = '';
            } elseif ($state === Router::T_PARAM && $c === '{') {
                throw new \InvalidArgumentException(
                    "Nested parameters are not allowed."
                );
            } elseif ($state === Router::T_PARAM) {
                $acc .= $c;
            }
        }
        if ($state === Router::T_PARAM) {
            throw new \InvalidArgumentException(
                "Missing closing bracket."
            );
        } elseif ($state === Router::T_STR && !empty($acc)) {
            $compiled[] = [Router::T_STR, $acc];
        }
        return $compiled;
    }

    public function match($compiled, $path) {
        $params = [];
        $i = 0;
        $j = 0;
        for ($j = 0; $j < count($compiled); $j++) {
            list($type, $val) = $compiled[$j];
            $nType = null;
            $nVal = null;
            if (isset($compiled[$j + 1])) {
                list($nType, $nVal) = $compiled[$j + 1];
            }
            if ($type === Router::T_STR) {
                if (strpos($path, $val, $i) !== $i) {
                    return false;
                }
                $i += strlen($val);
            } elseif ($type === Router::T_PARAM && $nType === null) {
                $params[$val] = substr($path, $i);
                $i = strlen($path);
            } elseif ($type === Router::T_PARAM && $nType === Router::T_PARAM) {
                $params[$val] = '';
            } elseif ($type === Router::T_PARAM && $nType === Router::T_STR) {
                $begin = $i;
                $end = strpos($path, $nVal, $i);
                if ($end === -1) {
                    return false;
                }
                $params[$val] = substr($path, $begin, $end - $begin);
                $i = $end;
            }
        }
        if ($i !== strlen($path)) {
            return false;
        }
        return $params;
    }
}
