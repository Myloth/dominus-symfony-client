<?php

namespace App\Dto\Users;

use JMS\Serializer\Annotation as JMS;

class GroupSearch
{

    #[JMS\Type('string')]
    public ?string $name  = null;
    #[JMS\Type('array')]
    public array $roles = [];

    public function getFilters(): array
    {
        return [
            'name' => $this->name,
            'roles' => $this->roles
        ];
    }
}