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
use Pingframework\Web\Annotations\RequestHeader;
use Pingframework\Web\Annotations\RequestHeaders;
use Pingframework\Web\Annotations\RequestPostParam;
use Pingframework\Web\Annotations\RequestPostParams;
use Pingframework\Web\Annotations\RequestQueryParam;
use Pingframework\Web\Annotations\RequestQueryParams;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author    Oleg Bronzov <oleg@bbumgames.com>
 * @copyright 2022
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ControllerGroup('/test')]
class TestController
{
    #[Controller('/test/{id:[0-9]+}', methods: ['GET'])]
    public function test1(
        string $id
    ): int {
        return (int)$id;
    }

    #[Controller('/test2', methods: ['POST'])]
    public function test2(
        #[RequestBody]
        string $body
    ): string {
        return $body;
    }

    #[Controller('/test3', methods: ['POST'])]
    public function test3(
        #[RequestBodyJson]
        array $payload
    ): array {
        return $payload;
    }

    #[Controller('/test4', methods: ['POST'])]
    public function test4(
        #[RequestBodySchema]
        TestRequestBodySchema $schema
    ): TestRequestBodySchema {
        return $schema;
    }

    #[Controller('/test5', methods: ['POST'])]
    public function test5(
        #[RequestBodySchemaList(TestRequestBodySchema::class)]
        array $schemas
    ): array {
        return $schemas;
    }

    #[Controller('/test6', methods: ['GET'])]
    public function test6(
        #[RequestQueryParams]
        array $queryParams,
    ): array {
        return $queryParams;
    }

    #[Controller('/test7', methods: ['GET'])]
    public function test7(
        #[RequestQueryParam('foo')]
        string $queryParam,
    ): string {
        return $queryParam;
    }

    #[Controller('/test8', methods: ['POST'])]
    public function test8(
        #[RequestPostParams]
        array $postParams,
    ): array {
        return $postParams;
    }

    #[Controller('/test9', methods: ['POST'])]
    public function test9(
        #[RequestPostParam]
        string $foo,
    ): string {
        return $foo;
    }

    #[Controller('/test10', methods: ['GET'])]
    public function test10(
        #[RequestHeaders]
        array $headers,
    ): array {
        return $headers;
    }

    #[Controller('/test11', methods: ['GET'])]
    public function test11(
        #[RequestHeader('X-Foo')]
        string $header,
    ): string {
        return $header;
    }
}