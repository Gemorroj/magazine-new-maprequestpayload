<?php

declare(strict_types=1);

namespace App\Controller\Api;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Response(
    response: 400,
    description: 'Bad Request',
    content: new OA\JsonContent(
        required: ['status', 'title', 'detail'],
        properties: [
            new OA\Property(property: 'status', type: 'integer', example: 400, nullable: false),
            new OA\Property(property: 'title', type: 'string', example: 'Bad Request', nullable: false),
            new OA\Property(property: 'detail', type: 'string', nullable: false),
        ],
        type: 'object',
        nullable: false,
    )
)]
#[OA\Response(
    response: 401,
    description: 'Unauthorized',
    content: new OA\JsonContent(
        required: ['code', 'message'],
        properties: [
            new OA\Property(property: 'code', type: 'integer', example: 401, nullable: false),
            new OA\Property(property: 'message', type: 'string', nullable: false),
        ],
        type: 'object',
        nullable: false,
    )
)]
final class AuthController extends AbstractController
{
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['username', 'password'],
            properties: [
                new OA\Property(property: 'username', type: 'string', nullable: false),
                new OA\Property(property: 'password', type: 'string', format: 'password', nullable: false),
            ],
            type: 'object',
            nullable: false
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            required: ['token'],
            properties: [
                new OA\Property(property: 'token', type: 'string', nullable: false),
            ],
            type: 'object',
            nullable: false,
        )
    )]
    #[Route('/api/auth', defaults: ['_format' => 'json'], methods: ['POST'])]
    public function __invoke(): void
    {
    }
}
