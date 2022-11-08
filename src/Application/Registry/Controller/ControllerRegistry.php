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

namespace Pingframework\Web\Application\Registry\Controller;

/**
 * @author    Oleg Bronzov <oleg@bbumgames.com>
 * @copyright 2022
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ControllerRegistry
{
    /**
     * @var array<string, ControllerDefinition>
     */
    private array $routs = [];
    /**
     * @var array<string, array<string, ControllerDefinition>>
     */
    private array $groups = [];

    public function put(
        string  $route,
        string  $class,
        string  $method,
        ?string $routeName,
        array   $httpMethods,
        ?string $group = null,
    ): void {
        $definition = new ControllerDefinition($route, $class, $method, $httpMethods, $routeName);

        if ($group !== null) {
            $this->groups[$group][$route] = $definition;
        } else {
            $this->routs[$route] = $definition;
        }
    }

    /**
     * @return array<string, ControllerDefinition>
     */
    public function getRouts(): array
    {
        return $this->routs;
    }

    /**
     * @return array<string, array<string, ControllerDefinition>>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}