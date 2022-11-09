<?php

/**
 * Ping Web
 *
 * MIT License
 * 
 * Copyright (c) 2022 pingframework
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */

declare(strict_types=1);

namespace Pingframework\Web\Application\Registry\Middleware;

use Exception;
use Pingframework\Ping\Annotations\Service;
use Pingframework\Web\Annotations\Middleware;
use Pingframework\Web\Http\Middleware\MiddlewareInterface;
use ReflectionClass;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
#[Service]
class MiddlewareRegistry
{
    /**
     * @var array<string, array<MiddlewareInterface>>
     */
    private array $mdRoutes = [];
    /**
     * @var array<string, array<MiddlewareInterface>>
     */
    private array $mdGroups = [];
    /**
     * @var MiddlewareInterface[]
     */
    private array $mdGlobal = [];

    public function __construct(
        MiddlewareInterface ...$middlewares
    ) {
        foreach ($middlewares as $middleware) {
            $this->put($middleware);
        }
    }

    /**
     * @param string $groupPattern
     * @return MiddlewareInterface[]
     */
    public function getGroups(string $groupPattern): array
    {
        return $this->mdGroups[$groupPattern] ?? [];
    }

    /**
     * @param string $routePattern
     * @return MiddlewareInterface[]
     */
    public function getRoutes(string $routePattern): array
    {
        return $this->mdRoutes[$routePattern] ?? [];
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function getGlobal(): array
    {
        return $this->mdGlobal;
    }

    public function put(MiddlewareInterface $middleware): void
    {
        $attribute = $this->getAttribute($middleware);

        if ($attribute->groupPattern !== null) {
            $this->mdGroups[$attribute->groupPattern][] = $middleware;
            return;
        }

        if ($attribute->routePattern !== null) {
            $this->mdRoutes[$attribute->routePattern][] = $middleware;
            return;
        }

        $this->mdGlobal[] = $middleware;
    }

    /**
     * @throws Exception
     */
    private function getAttribute(MiddlewareInterface $middleware): Middleware
    {
        $attributes = (new ReflectionClass($middleware))->getAttributes(Middleware::class);
        if (count($attributes) === 0) {
            throw new Exception('Middleware must have Middleware annotation');
        }
        return $attributes[0]->newInstance();
    }
}