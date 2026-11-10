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

    #[Assert\Count(min: 1, minMessage: "Veuillez sélectionner au moins un type d'entité.")]
    public array $entityTypes = [];

    public ?string $category = null;

    public ?\DateTimeImmutable $createdAt = null;

    public function getFormattedCreatedAt(): string
    {
        return $this->createdAt?->format('d/m/Y H:i') ?? '-';
    }

    /**
     * @return list<EntityType>
     */
    public function getNormalizedEntityTypes(): array
    {
        $normalized = [];

        foreach ($this->entityTypes as $item) {
            if ($item instanceof EntityType) {
                $normalized[] = $item;
            } elseif (is_array($item)) {
                $et = new EntityType();
                $et->apiId = isset($item['@id']) && is_string($item['@id']) ? $item['@id'] : null;
                $et->id = isset($item['id']) ? (int) $item['id'] : null;
                $et->name = isset($item['name']) && is_string($item['name']) ? $item['name'] : null;
                $et->slug = isset($item['slug']) && is_string($item['slug']) ? $item['slug'] : null;
                $normalized[] = $et;
            } elseif (is_string($item) && trim($item) !== '') {
                $et = new EntityType();
                $et->apiId = trim($item);
                $et->slug = trim($item);
                $et->name = trim($item);
                $normalized[] = $et;
            }
        }

        return $normalized;
    }

    /**
     * @return list<string>
     */
    public function getEntityTypeIris(): array
    {
        $iris = [];

        foreach ($this->entityTypes as $item) {
            if (is_string($item) && trim($item) !== '') {
                $iris[] = trim($item);
            } elseif ($item instanceof EntityType) {
                $iri = $item->getIri();
                if ($iri !== '') {
                    $iris[] = $iri;
                }
            } elseif (is_array($item)) {
                if (isset($item['@id']) && is_string($item['@id']) && $item['@id'] !== '') {
                    $iris[] = $item['@id'];
                } elseif (isset($item['id'])) {
                    $iris[] = '/entity_types/' . $item['id'];
                }
            }
        }

        return array_values(array_unique($iris));
    }

    public function hasEntityTypeSlug(string $slug): bool
    {
        foreach ($this->getNormalizedEntityTypes() as $entityType) {
            if ($entityType->slug === $slug) {
                return true;
            }
        }

        return false;
    }
}
