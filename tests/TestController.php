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
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
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