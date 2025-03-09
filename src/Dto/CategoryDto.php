<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Category;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CategoryDto
{
    public function __construct(
        #[OA\Property(readOnly: true, nullable: false)]
        #[Groups(['category:read'])]
        public ?\DateTimeImmutable $createdAt,
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 255)]
        #[Groups(['category:read', 'category:create', 'category:update'])]
        public string $name,
        #[OA\Property(readOnly: true)]
        #[Groups(['category:read'])]
        public ?int $id = null,
        #[OA\Property(readOnly: true)]
        #[Groups(['category:read'])]
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
    }

    public static function fromEntity(Category $category): self
    {
        return new self(
            $category->getCreatedAt(),
            $category->getName(),
            $category->getId(),
            $category->getUpdatedAt(),
        );
    }

    public function hydrate(Category $category): void
    {
        $category->setName($this->name);
    }
}
