<?php

namespace App\Dto\Events;

use App\Dto\SearchDtoInterface;
use Symfony\Component\Serializer\Attribute\Ignore;

class EventSearch implements SearchDtoInterface
{
    public string $name = '';
    public string $active = '';
    public string $period = '';
    public string $startDate = '';
    public string $endDate = '';
    public int $page = 1;
    public int $itemsPerPage = 10;

    #[Ignore]
    public function hasCustomFilters(): bool
    {
        return trim($this->name) !== ''
            || $this->active !== ''
            || trim($this->period) !== ''
            || trim($this->startDate) !== ''
            || trim($this->endDate) !== '';
    }

    #[Ignore]
    public function getFilters(): array
    {
        $filters = [];

        if (trim($this->name) !== '') {
            $filters['name'] = trim($this->name);
        }

        if ($this->active !== '') {
            $filters['active'] = ($this->active === '1' || $this->active === 'true') ? 'true' : 'false';
        }

        if (trim($this->startDate) !== '') {
            $filters['startDate[after]'] = trim($this->startDate);
        }

        if (trim($this->endDate) !== '') {
            $filters['endDate[before]'] = trim($this->endDate);
        }

        if (trim($this->period) !== '') {
            $filters['period'] = trim($this->period);
        }

        $filters['page'] = max(1, $this->page);
        $filters['itemsPerPage'] = max(1, $this->itemsPerPage);

        return $filters;
    }
}
