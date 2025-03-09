<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Dto\ProductDto;
use App\Entity\Product;
use App\Pagination\Paginator;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Security\Voter\ProductVoter;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Security(name: 'Bearer')]
#[OA\Tag(name: 'products')]
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
    response: 403,
    description: 'Access Denied',
    content: new OA\JsonContent(
        required: ['status', 'title', 'detail'],
        properties: [
            new OA\Property(property: 'status', type: 'integer', example: 403, nullable: false),
            new OA\Property(property: 'title', type: 'string', example: 'Access Denied', nullable: false),
            new OA\Property(property: 'detail', type: 'string', nullable: false),
        ],
        type: 'object',
        nullable: false,
    )
)]
#[OA\Response(
    response: 404,
    description: 'Not Found',
    content: new OA\JsonContent(
        required: ['status', 'title', 'detail'],
        properties: [
            new OA\Property(property: 'status', type: 'integer', example: 404, nullable: false),
            new OA\Property(property: 'title', type: 'string', example: 'Not Found', nullable: false),
            new OA\Property(property: 'detail', type: 'string', nullable: false),
        ],
        type: 'object',
        nullable: false,
    )
)]
#[OA\Response(
    response: 422,
    description: 'Unprocessable Entity',
    content: new OA\JsonContent(
        required: ['status', 'title', 'detail', 'violations'],
        properties: [
            new OA\Property(property: 'status', type: 'integer', example: 422, nullable: false),
            new OA\Property(property: 'title', type: 'string', example: 'Unprocessable Entity', nullable: false),
            new OA\Property(property: 'detail', type: 'string', nullable: false),
            new OA\Property(property: 'violations', type: 'array', items: new OA\Items(
                required: ['propertyPath', 'title', 'template', 'parameters'],
                properties: [
                    new OA\Property(property: 'propertyPath', type: 'string', nullable: false),
                    new OA\Property(property: 'title', type: 'string', nullable: false),
                    new OA\Property(property: 'template', type: 'string', nullable: false),
                    new OA\Property(property: 'parameters', properties: [
                        new OA\Property(property: '{{ type }}', type: 'string', nullable: false),
                        new OA\Property(property: 'hint', type: 'string', nullable: false),
                    ], type: 'object', nullable: false),
                ],
                type: 'object',
                nullable: false,
            ), nullable: false),
        ],
        type: 'object',
        nullable: false,
    )
)]
#[Route('/api/products')]
final class ProductController extends AbstractController
{
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            required: ['totalItems', 'member'],
            properties: [
                new OA\Property(property: 'totalItems', type: 'integer', minimum: 0, readOnly: true, nullable: false),
                new OA\Property(property: 'member', type: 'array', items: new OA\Items(ref: new Model(type: ProductDto::class, groups: ['product:read']), nullable: false), readOnly: true, nullable: false),
            ],
            type: 'object',
            nullable: false,
        )
    )]
    #[Route('', methods: ['GET'], format: 'json')]
    public function collection(#[MapQueryParameter] int $page, ProductRepository $productRepository): JsonResponse
    {
        $queryBuilder = $productRepository->createQueryBuilder('product');
        $paginator = (new Paginator($queryBuilder))->paginate($page);
        $productsDto = [];
        foreach ($paginator->getResults() as $product) {
            $this->denyAccessUnlessGranted(ProductVoter::VIEW, $product);
            $productsDto[] = ProductDto::fromEntity($product);
        }

        return $this->json([
            'totalItems' => $paginator->getNumResults(),
            'member' => $productsDto,
        ], context: ['groups' => ['product:read']]);
    }

    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            ref: new Model(type: ProductDto::class, groups: ['product:read']),
            nullable: false,
        ),
    )]
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'], format: 'json')]
    public function get(Product $product): JsonResponse
    {
        $this->denyAccessUnlessGranted(ProductVoter::VIEW, $product);

        $dto = ProductDto::fromEntity($product);

        return $this->json($dto, context: ['groups' => ['product:read']]);
    }

    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            ref: new Model(type: ProductDto::class, groups: ['product:read']),
            nullable: false,
        ),
    )]
    #[Route('', methods: ['POST'], format: 'json')]
    public function post(#[MapRequestPayload(validationGroups: 'product:create')] ProductDto $productDto, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = new Product();
        $productDto->hydrate($product, $categoryRepository);

        $this->denyAccessUnlessGranted(ProductVoter::EDIT, $product);

        $entityManager->persist($product);
        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw HttpException::fromStatusCode(422, 'The Product already exists.', $e);
        }

        $dto = ProductDto::fromEntity($product);

        return $this->json($dto, context: ['groups' => ['product:read']]);
    }

    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            ref: new Model(type: ProductDto::class, groups: ['product:read']),
            nullable: false,
        ),
    )]
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'], format: 'json')]
    public function put(Product $product, #[MapRequestPayload(validationGroups: 'product:update')] ProductDto $productDto, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted(ProductVoter::EDIT, $product);

        $productDto->hydrate($product, $categoryRepository);
        $entityManager->persist($product);
        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw HttpException::fromStatusCode(422, 'The Product already exists.', $e);
        }

        $dto = ProductDto::fromEntity($product);

        return $this->json($dto, context: ['groups' => ['product:read']]);
    }
}
