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

use Pingframework\Ping\Annotations\Service;
use Pingframework\Ping\DependencyContainer\DependencyContainerInterface;
use Pingframework\Web\Application\CustomErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Throwable;

/**
 * @author    Oleg Bronzov <oleg@bbumgames.com>
 * @copyright 2022
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[Service(CustomErrorHandlerInterface::class)]
class TestCustomErrorHandler implements CustomErrorHandlerInterface
{
    public function handle(
        ServerRequestInterface       $request,
        Throwable                    $exception,
        DependencyContainerInterface $c,
        bool                         $displayErrorDetails,
        bool                         $logErrors,
        bool                         $logErrorDetails,
    ): ResponseInterface {
        $c->get(LoggerInterface::class)->error($exception->getMessage());

        $payload = ['error' => $exception->getMessage()];

        $response = $c->get(App::class)->getResponseFactory()->createResponse(404);
        $response->getBody()->write(
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        return $response;
    }
}