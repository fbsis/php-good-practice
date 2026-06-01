<?php

declare(strict_types=1);

namespace Challenge\Controllers;

use Challenge\Http\JsonResponder;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class HealthController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $this->pdo->query('SELECT 1')->fetchColumn();

        return JsonResponder::json($response, [
            'status' => 'ok',
            'database' => 'connected',
        ]);
    }
}
