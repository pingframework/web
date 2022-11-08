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

namespace Pingframework\Web\Application;

use Pingframework\Boot\Annotations\PingBootApplication;
use Pingframework\Boot\Application\AbstractPingBootApplication;
use Pingframework\Ping\Annotations\Autowired;
use Pingframework\Ping\Annotations\Inject;
use Pingframework\Ping\Utils\Json\JsonEncoderInterface;
use Pingframework\Ping\Utils\ObjectMapper\ObjectMapperInterface;
use Pingframework\Web\Application\Registry\Controller\ControllerDefinition;
use Pingframework\Web\Application\Registry\Controller\ControllerRegistry;
use Pingframework\Web\Application\Registry\Middleware\MiddlewareRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteCollectorProxy;
use Throwable;

/**
 * @author    Oleg Bronzov <oleg@bbumgames.com>
 * @copyright 2022
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[PingBootApplication]
class WebApplication extends AbstractPingBootApplication
{
    public const CONFIG_KEY_DISPLAY_ERROR_DETAILS = 'slim.display_error_details';
    public const CONFIG_KEY_LOG_ERRORS            = 'slim.log_errors';
    public const CONFIG_KEY_LOG_ERROR_DETAILS     = 'slim.log_error_details';
    public const CONFIG_KEY_BODY_PARSERS          = 'slim.body_parsers';
    public const CONFIG_KEY_CACHE_FILE            = 'slim.cache_file';

    #[Autowired]
    public function configure(
        ControllerRegistry           $controllerRegistry,
        MiddlewareRegistry           $middlewareRegistry,
        LoggerInterface              $logger,
        #[Inject(self::CONFIG_KEY_DISPLAY_ERROR_DETAILS)]
        bool                         $displayErrorDetails = false,
        #[Inject(self::CONFIG_KEY_LOG_ERRORS)]
        bool                         $logErrors = false,
        #[Inject(self::CONFIG_KEY_LOG_ERROR_DETAILS)]
        bool                         $logErrorDetails = false,
        #[Inject(self::CONFIG_KEY_BODY_PARSERS)]
        bool                         $bodyParsers = true,
        #[Inject(self::CONFIG_KEY_CACHE_FILE)]
        ?string                      $cacheFile = null,
        ?CustomErrorHandlerInterface $customErrorHandler = null,
    ): void {
        $this->getApplicationContext()->setIfNotExists(
            App::class,
            AppFactory::createFromContainer($this->getApplicationContext())
        );
        $slim = $this->getApplicationContext()->get(App::class);

        /**
         * To generate the route cache data, you need to set the file to one that does not exist in a writable directory.
         * After the file is generated on first run, only read permissions for the file are required.
         *
         * You may need to generate this file in a development environment and committing it to your project before deploying
         * if you don't have write permissions for the directory where the cache file resides on the server it is being deployed to
         */
        if ($cacheFile !== null) {
            $routeCollector = $slim->getRouteCollector();
            $routeCollector->setCacheFile($cacheFile);
        }

        if ($bodyParsers) {
            $slim->addBodyParsingMiddleware();
        }

        /**
         * The routing middleware should be added earlier than the ErrorMiddleware
         * Otherwise exceptions thrown from it will not be handled by the middleware
         */
        $slim->addRoutingMiddleware();

        // register global middlewares
        foreach ($middlewareRegistry->getGlobal() as $middleware) {
            $slim->add(
                fn(
                    ServerRequestInterface  $request,
                    RequestHandlerInterface $handler
                ): ResponseInterface => $middleware->handle($request, $handler)
            );
        }

        // register groups and group's routes
        $self = $this;
        foreach ($controllerRegistry->getGroups() as $groupPattern => $routes) {
            $group = $slim->group($groupPattern, function (RouteCollectorProxy $group) use ($routes, $self) {
                foreach ($routes as $cd) {
                    $self->setRoute($group, $cd);
                }
            });

            foreach ($middlewareRegistry->getGroups($groupPattern) as $middleware) {
                $group->add(
                    fn(
                        ServerRequestInterface  $request,
                        RequestHandlerInterface $handler
                    ): ResponseInterface => $middleware->handle($request, $handler)
                );
            }
        }

        // register routes
        foreach ($controllerRegistry->getRouts() as $controllerDefinition) {
            $route = $this->setRoute($slim, $controllerDefinition);

            foreach ($middlewareRegistry->getRoutes($controllerDefinition->route) as $middleware) {
                $route->add(
                    fn(
                        ServerRequestInterface  $request,
                        RequestHandlerInterface $handler
                    ): ResponseInterface => $middleware->handle($request, $handler)
                );
            }
        }

        /**
         * Add Error Middleware
         *
         * @param bool                 $displayErrorDetails -> Should be set to false in production
         * @param bool                 $logErrors           -> Parameter is passed to the default ErrorHandler
         * @param bool                 $logErrorDetails     -> Display error details in error log
         * @param LoggerInterface|null $logger              -> Optional PSR-3 Logger
         *
         * Note: This middleware should be added last. It will not handle any exceptions/errors
         * for middleware added after it.
         */
        $errorMiddleware = $slim->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails, $logger);
        if ($customErrorHandler !== null) {
            $errorMiddleware->setDefaultErrorHandler(function (
                ServerRequestInterface $request,
                Throwable              $exception,
                bool                   $displayErrorDetails,
                bool                   $logErrors,
                bool                   $logErrorDetails,
            ) use ($self, $customErrorHandler): ResponseInterface {
                return $customErrorHandler->handle(
                    $request,
                    $exception,
                    $self->getApplicationContext(),
                    $displayErrorDetails,
                    $logErrors,
                    $logErrorDetails,
                );
            });
        }
    }

    private function setRoute(
        RouteCollectorProxy|App $proxy,
        ControllerDefinition    $controllerDefinition
    ): RouteInterface {
        $route = $proxy->map(
            $controllerDefinition->httpMethods,
            $controllerDefinition->route,
            $this->getCallback($controllerDefinition)
        );

        if ($controllerDefinition->routeName !== null) {
            $route->setName($controllerDefinition->routeName);
        }

        return $route;
    }

    private function getCallback(ControllerDefinition $cd): callable
    {
        $self = $this;
        return function (
            ServerRequestInterface $request,
            ResponseInterface      $response,
            array                  $args
        ) use (
            $cd,
            $self
        ): ResponseInterface {
            return $self->processResult(
                $response,
                $self->getApplicationContext()->call(
                    $self->getApplicationContext()->get($cd->class),
                    $cd->method,
                    array_merge($args, [
                        'request'                     => $request,
                        ServerRequestInterface::class => $request,
                        'response'                    => $response,
                        ResponseInterface::class      => $response,
                        'args'                        => $args,
                    ])
                )
            );
        };
    }

    private function processResult(ResponseInterface $response, mixed $result): ResponseInterface
    {
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        if (is_string($result) || is_numeric($result)) {
            $response->getBody()->write((string)$result);
            return $response;
        }

        if (is_bool($result)) {
            $response->getBody()->write((string)(int)$result);
            return $response;
        }

        if (is_array($result)) {
            $response->getBody()->write(
                $this->isUnmappableList($result)
                    ? $this->getApplicationContext()->get(ObjectMapperInterface::class)->unmapListToJson($result)
                    : $this->getApplicationContext()->get(JsonEncoderInterface::class)->marshal($result)
            );
            return $response;
        }

        if (is_object($result)) {
            $response->getBody()->write(
                $this->getApplicationContext()->get(ObjectMapperInterface::class)->unmapToJson($result)
            );
            return $response;
        }

        return $response;
    }

    private function isUnmappableList(mixed $result): bool
    {
        if (!is_array($result)) {
            return false;
        }

        if (count($result) === 0) {
            return false;
        }

        if (is_object(reset($result))) {
            return true;
        }

        return false;
    }
}