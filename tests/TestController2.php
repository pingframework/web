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

namespace Pingframework\Web\Tests;

use Pingframework\Web\Annotations\Controller;
use Pingframework\Web\Annotations\ControllerGroup;
use Pingframework\Web\Annotations\RequestBody;
use Pingframework\Web\Annotations\RequestBodyJson;
use Pingframework\Web\Annotations\RequestBodySchema;
use Pingframework\Web\Annotations\RequestBodySchemaList;

/**
 * @author    Oleg Bronzov <oleg@bbumgames.com>
 * @copyright 2022
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ControllerGroup('/test-group')]
class TestController2
{
    #[Controller('/test/{id:[0-9]+}', methods: ['GET'])]
    public function test1(
        string $id
    ): int {
        return (int)$id;
    }
}