<?php

namespace App\Dto\Tags;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class Tag
{
    #[SerializedName('@id')]
    public ?string $apiId = null;

    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    public ?string $name = null;

    public ?string $slug = null;

    #[Assert\NotBlank(message: "Le type d'entité est obligatoire.")]
    public ?string $entityType = null;

    public ?string $category = null;

    public ?\DateTimeImmutable $createdAt = null;

    public function getFormattedCreatedAt(): string
    {
        return $this->createdAt?->format('d/m/Y H:i') ?? '-';
    }
}
