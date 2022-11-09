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

namespace Pingframework\Web\Annotations;

use Attribute;
use Pingframework\Ping\Annotations\MethodRegistrar;
use Pingframework\Ping\DependencyContainer\DependencyContainerInterface;
use Pingframework\Web\Application\Registry\Controller\ControllerRegistry;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Controller implements MethodRegistrar
{
    public function __construct(
        public readonly string  $route,
        public readonly array   $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        public readonly ?string $name = null,
        public readonly ?string $group = null,
    ) {}

    public function registerMethod(
        DependencyContainerInterface $c,
        ReflectionClass              $rc,
        ReflectionMethod             $rm,
    ): void {
        $c->get(ControllerRegistry::class)->put(
            $this->route,
            $rc->getName(),
            $rm->getName(),
            $this->name,
            $this->methods,
            $this->getGroup($rc),
        );
    }

    private function getGroup(ReflectionClass $rc): ?string
    {
        if ($this->group !== null) {
            return $this->group;
        }

        foreach ($rc->getAttributes(ControllerGroup::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            return $attribute->newInstance()->route;
        }

        return null;
    }
}