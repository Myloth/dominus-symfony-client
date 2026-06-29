<?php

namespace App\Dto\Users;

use Symfony\Component\Serializer\Annotation as Serializer;
use App\Dto\SearchDtoInterface;

class UserSearch implements SearchDtoInterface
{
    public ?string $name = null;

    public array $group = [];

    public function getFilters(): array
    {
        return [
            'name' => $this->name,
            'group' => $this->group
        ];
    }
}
