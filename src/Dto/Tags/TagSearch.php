<?php

namespace App\Dto\Tags;

use App\Dto\SearchDtoInterface;
use Symfony\Component\Serializer\Attribute\Ignore;

class TagSearch implements SearchDtoInterface
{
    public string $name = '';
    public string $slug = '';
    public string $entityType = '';
    public int $page = 1;
    public int $itemsPerPage = 10;

    #[Ignore]
    public function hasCustomFilters(): bool
    {
        return trim($this->name) !== ''
            || trim($this->slug) !== ''
            || trim($this->entityType) !== '';
    }

    #[Ignore]
    public function getFilters(): array
    {
        $filters = [];

        if (trim($this->name) !== '') {
            $filters['name'] = trim($this->name);
        }

        if (trim($this->slug) !== '') {
            $filters['slug'] = trim($this->slug);
        }

        if (trim($this->entityType) !== '') {
            $entityTypeFilter = trim($this->entityType);
            if (str_starts_with($entityTypeFilter, '/entity_types/')) {
                $filters['entityTypes'] = $entityTypeFilter;
            } else {
                $filters['entityTypes.slug'] = $entityTypeFilter;
            }
        }

        $filters['page'] = max(1, $this->page);
        $filters['itemsPerPage'] = max(1, $this->itemsPerPage);

        return $filters;
    }
}
