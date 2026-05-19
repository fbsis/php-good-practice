<?php

declare(strict_types=1);

namespace Challenge\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

final class HttpFactory
{
    public static function responseFactory(): ResponseFactoryInterface
    {
        return new ResponseFactory();
    }

    public static function serverRequestFactory(): ServerRequestFactoryInterface
    {
        return new ServerRequestFactory();
    }
}
