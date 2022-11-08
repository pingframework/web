<?php

/**
 * Ping Web
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * Json RPC://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phpsuit.net so we can send you a copy immediately.
 *
 * @author    Oleg Bronzov <oleg@bbumgames.com>
 * @copyright 2022
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace Pingframework\Web\Application\Registry\Middleware;

use Exception;
use Pingframework\Ping\Annotations\Service;
use Pingframework\Web\Annotations\Middleware;
use Pingframework\Web\Http\Middleware\MiddlewareInterface;
use ReflectionClass;

/**
 * @author    Oleg Bronzov <oleg@bbumgames.com>
 * @copyright 2022
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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