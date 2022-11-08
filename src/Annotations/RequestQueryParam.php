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

namespace Pingframework\Web\Annotations;

use Attribute;
use Pingframework\Ping\Annotations\Injector;
use Pingframework\Ping\DependencyContainer\Definition\VariadicDefinitionMap;
use Pingframework\Ping\DependencyContainer\DependencyContainerInterface;
use Pingframework\Ping\Utils\Arrays\Arrays;
use Pingframework\Ping\Utils\Strings\Strings;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use RuntimeException;

/**
 * @author    Oleg Bronzov <oleg@bbumgames.com>
 * @copyright 2022
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestQueryParam implements Injector
{
    public function __construct(
        public readonly ?string $name = null,
    ) {}

    public function inject(
        DependencyContainerInterface           $c,
        VariadicDefinitionMap                  $vdm,
        ReflectionClass                        $rc,
        ?ReflectionMethod                      $rm,
        ReflectionParameter|ReflectionProperty $rp,
        array                                  $runtime
    ): mixed {
        $queryParams = $runtime[ServerRequestInterface::class]->getQueryParams();

        $name = $this->name ?? Strings::camelCaseToUnderscore($rp->getName());

        if (!Arrays::isExists($queryParams, $name)) {
            if ($rp->allowsNull()) {
                return null;
            }
            throw new RuntimeException("Query param '{$name}' is not exists.");
        }

        return $queryParams[$name];
    }
}