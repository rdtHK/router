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
class Route
{
    private $method;
    private $path;
    private $parameters;
    private $controller;

    public function __construct(?string $method, string $path, $controller)
    {
        $this->method = $method;
        $this->path = $path;
        $this->parameters = [];
        $this->controller = $controller;

        // find all parameters
        preg_match_all('/:(?<param>[a-zA-Z_][a-zA-Z_0-9]*)/', $path, $matches);

        foreach ($matches['param'] as $match) {
            $this->parameters[$match] = '.*';
        }
    }

    public function param(string $name, string $regex): Route
    {
        $this->parameters[$name] = $regex;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getController()
    {
        return $this->controller;
    }

}
