<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ProductDto
{
    public function __construct(
        #[OA\Property(readOnly: true, nullable: false)]
        #[Groups(['product:read'])]
        public ?\DateTimeImmutable $createdAt,
        #[Assert\NotNull]
        #[Groups(['product:read', 'product:create', 'product:update'])]
        public int $category,
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 255)]
        #[Groups(['product:read', 'product:create', 'product:update'])]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 5000)]
        #[Groups(['product:read', 'product:create', 'product:update'])]
        public string $description,
        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        #[Groups(['product:read', 'product:create', 'product:update'])]
        public string $price,
        #[Assert\Length(max: 255)]
        #[Groups(['product:read', 'product:create', 'product:update'])]
        public ?string $size = null,
        #[Assert\Length(max: 255)]
        #[Groups(['product:read', 'product:create', 'product:update'])]
        public ?string $composition = null,
        #[Assert\Length(max: 255)]
        #[Groups(['product:read', 'product:create', 'product:update'])]
        public ?string $manufacturer = null,
        /**
         * @var string|null $descriptionInternal Property viewable and writable only by users with ROLE_ADMIN
         */
        #[Assert\Length(max: 5000)]
        #[Groups(['product:read', 'product:create', 'product:update'])]
        public ?string $descriptionInternal = null,
        #[OA\Property(readOnly: true)]
        #[Groups(['product:read'])]
        public ?int $id = null,
        #[OA\Property(readOnly: true)]
        #[Groups(['product:read'])]
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
    }

    public static function fromEntity(Product $product): self
    {
        return new self(
            $product->getCreatedAt(),
            $product->getCategory()->getId(),
            $product->getName(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getSize(),
            $product->getComposition(),
            $product->getManufacturer(),
            $product->getDescriptionInternal(),
            $product->getId(),
            $product->getUpdatedAt(),
        );
    }

    public function toEntity(Product $product, CategoryRepository $categoryRepository): Product
    {
        $category = $categoryRepository->find($this->category);
        if (!$category) {
            throw HttpException::fromStatusCode(404, 'Invalid category '.$this->category);
        }
        $product->setCategory($category);
        $product->setName($this->name);
        $product->setDescription($this->description);
        $product->setPrice($this->price);
        $product->setSize($this->size);
        $product->setComposition($this->composition);
        $product->setManufacturer($this->manufacturer);
        $product->setDescriptionInternal($this->descriptionInternal);

        return $product;
    }
}
