<?php

namespace App\Dto\Tags;

class TagPaginatedResponse
{
    /**
     * @param list<Tag> $items
     */
    public function __construct(
        public array $items = [],
        public int $totalItems = 0,
        public int $page = 1,
        public int $itemsPerPage = 10,
    ) {
    }

    public function getTotalPages(): int
    {
        if ($this->itemsPerPage <= 0) {
            return 1;
        }

        return max(1, (int) ceil($this->totalItems / $this->itemsPerPage));
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->getTotalPages();
    }

    public function getStartIndex(): int
    {
        if ($this->totalItems === 0) {
            return 0;
        }

        return (($this->page - 1) * $this->itemsPerPage) + 1;
    }

    public function getEndIndex(): int
    {
        if ($this->totalItems === 0) {
            return 0;
        }

        return min($this->page * $this->itemsPerPage, $this->totalItems);
    }
}
