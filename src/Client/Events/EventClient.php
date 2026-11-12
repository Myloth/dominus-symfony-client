<?php

namespace App\Client\Events;

use App\Client\AbstractClient;
use App\Dto\Events\Event;
use App\Dto\Events\EventPaginatedResponse;
use App\Dto\Events\EventSearch;

class EventClient extends AbstractClient
{
    public function getPaginated(EventSearch $search): EventPaginatedResponse
    {
        $result = $this->requestPaginated('GET', 'events', Event::class, [
            'query' => $search->getFilters(),
        ]);

        /** @var list<Event> $items */
        $items = $result['items'];
        $totalItems = $result['totalItems'];

        if ($search->hasCustomFilters()) {
            $filtered = array_values(array_filter(
                $items,
                fn (Event $event): bool => $this->matchesSearch($event, $search)
            ));

            if (count($filtered) !== count($items)) {
                $totalItems = count($filtered);
                $offset = (max(1, $search->page) - 1) * max(1, $search->itemsPerPage);
                $items = array_slice($filtered, $offset, max(1, $search->itemsPerPage));
            }
        }

        if (count($items) > $search->itemsPerPage) {
            $offset = (max(1, $search->page) - 1) * max(1, $search->itemsPerPage);
            $items = array_slice($items, $offset, max(1, $search->itemsPerPage));
        }

        return new EventPaginatedResponse(
            items: $items,
            totalItems: $totalItems,
            page: $search->page,
            itemsPerPage: $search->itemsPerPage,
        );
    }

    /**
     * @return list<Event>
     */
    public function getAll(): array
    {
        return $this->request('GET', 'events', Event::class . '[]');
    }

    public function get(int $id): Event
    {
        return $this->request('GET', "events/{$id}", Event::class);
    }

    public function create(Event $event): Event
    {
        return $this->request('POST', 'events', Event::class, [
            'json' => $this->buildPayload($event),
        ]);
    }

    public function update(int $id, Event $event): Event
    {
        return $this->request('PATCH', "events/{$id}", Event::class, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => $this->buildPayload($event),
        ]);
    }

    public function updateActive(int $id, bool $active): Event
    {
        return $this->request('PATCH', "events/{$id}", Event::class, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'active' => $active,
            ],
        ]);
    }

    public function delete(int $id): void
    {
        $this->request('DELETE', "events/{$id}", null);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(Event $event): array
    {
        return [
            'name' => $event->name !== null ? trim($event->name) : '',
            'startDate' => $event->startDate?->format(\DateTimeInterface::ATOM),
            'endDate' => $event->endDate?->format(\DateTimeInterface::ATOM),
            'active' => $event->active,
            'description' => $event->description !== null && trim($event->description) !== ''
                ? trim($event->description)
                : null,
            'tags' => $event->getTagIris(),
        ];
    }

    private function matchesSearch(Event $event, EventSearch $search): bool
    {
        if ($search->name !== null && trim($search->name) !== '') {
            $needle = mb_strtolower(trim($search->name));
            $haystack = mb_strtolower($event->name ?? '');
            $descHaystack = mb_strtolower($event->description ?? '');
            if (!str_contains($haystack, $needle) && !str_contains($descHaystack, $needle)) {
                return false;
            }
        }

        if ($search->active !== null && $search->active !== '') {
            $expectedActive = ($search->active === '1' || $search->active === 'true');
            if ($event->active !== $expectedActive) {
                return false;
            }
        }

        if ($search->startDate !== null && trim($search->startDate) !== '') {
            try {
                $from = new \DateTimeImmutable(trim($search->startDate) . ' 00:00:00');
                $effectiveEnd = $event->endDate ?? $event->startDate;
                if ($effectiveEnd === null || $effectiveEnd < $from) {
                    return false;
                }
            } catch (\Throwable) {
            }
        }

        if ($search->endDate !== null && trim($search->endDate) !== '') {
            try {
                $to = new \DateTimeImmutable(trim($search->endDate) . ' 23:59:59');
                $effectiveStart = $event->startDate ?? $event->endDate;
                if ($effectiveStart === null || $effectiveStart > $to) {
                    return false;
                }
            } catch (\Throwable) {
            }
        }

        if ($search->period !== null && trim($search->period) !== '') {
            $now = new \DateTimeImmutable();
            $period = trim($search->period);

            if ($period === 'ongoing') {
                $started = $event->startDate === null || $event->startDate <= $now;
                $notEnded = $event->endDate === null || $event->endDate >= $now;
                if (!($started && $notEnded)) {
                    return false;
                }
            } elseif ($period === 'upcoming') {
                if ($event->startDate === null || $event->startDate <= $now) {
                    return false;
                }
            } elseif ($period === 'past') {
                if ($event->endDate === null || $event->endDate >= $now) {
                    return false;
                }
            }
        }

        return true;
    }
}
