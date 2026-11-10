<?php

namespace App\Dto\Tags;

use Symfony\Component\Serializer\Annotation\SerializedName;

class EntityType
{
    #[SerializedName('@id')]
    public ?string $apiId = null;

    public ?int $id = null;

    public ?string $name = null;

    public ?string $slug = null;

    public function getIri(): string
    {
        return $this->apiId ?? ($this->id !== null ? '/entity_types/' . $this->id : '');
    }
}
