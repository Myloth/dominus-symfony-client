<?php

namespace App\Dto\Tags;

use App\Dto\SearchDtoInterface;

class TagSearch implements SearchDtoInterface
{
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $entityType = null;
    public int $page = 1;
    public int $itemsPerPage = 10;

    public function getFilters(): array
    {
        $filters = [];

        if ($this->name !== null && trim($this->name) !== '') {
            $filters['name'] = trim($this->name);
        }

        if ($this->slug !== null && trim($this->slug) !== '') {
            $filters['slug'] = trim($this->slug);
        }

        if ($this->entityType !== null && trim($this->entityType) !== '') {
            $filters['entityType'] = trim($this->entityType);
        }

        $filters['page'] = max(1, $this->page);
        $filters['itemsPerPage'] = max(1, $this->itemsPerPage);

        return $filters;
    }
}
