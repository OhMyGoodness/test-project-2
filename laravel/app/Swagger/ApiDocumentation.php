<?php

declare(strict_types=1);

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "API documentation for my project",
    title: "My Laravel API"
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Local API server"
)]
class ApiDocumentation
{
    // пустой, используется только для аннотаций
}
