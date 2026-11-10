<?php

namespace App\Client\Tags;

use App\Client\AbstractClient;
use App\Dto\Tags\EntityType;
use App\Dto\Tags\Tag;
use App\Dto\Tags\TagPaginatedResponse;
use App\Dto\Tags\TagSearch;

class TagClient extends AbstractClient
{
    public function getPaginated(TagSearch $search): TagPaginatedResponse
    {
        $result = $this->requestPaginated('GET', 'tags', Tag::class, [
            'query' => $search->getFilters(),
        ]);

        return new TagPaginatedResponse(
            items: $result['items'],
            totalItems: $result['totalItems'],
            page: $search->page,
            itemsPerPage: $search->itemsPerPage,
        );
    }

    /**
     * @return list<Tag>
     */
    public function getAll(): array
    {
        return $this->request('GET', 'tags', Tag::class . '[]') ?? [];
    }

    /**
     * @return list<EntityType>
     */
    public function getEntityTypes(): array
    {
        return $this->request('GET', 'entity_types', EntityType::class . '[]') ?? [];
    }

    /**
     * @return list<Tag>
     */
    public function getByEntityType(string $entityType): array
    {
        $search = new TagSearch();
        $search->entityType = $entityType;
        $search->itemsPerPage = 100;

        $response = $this->getPaginated($search);

        return array_values(array_filter(
            $response->items,
            static fn (Tag $tag): bool => $tag->hasEntityTypeSlug($entityType)
        ));
    }

    public function get(int $id): Tag
    {
        return $this->request('GET', "tags/{$id}", Tag::class);
    }

    public function create(Tag $tag): Tag
    {
        $payload = [
            'name' => $tag->name,
            'entityTypes' => $tag->getEntityTypeIris(),
            'category' => $tag->category !== null && trim($tag->category) !== '' ? trim($tag->category) : null,
        ];

        if ($tag->slug !== null && trim($tag->slug) !== '') {
            $payload['slug'] = trim($tag->slug);
        }

        return $this->request('POST', 'tags', Tag::class, [
            'json' => $payload,
        ]);
    }

    public function update(int $id, Tag $tag): Tag
    {
        $payload = [
            'name' => $tag->name,
            'entityTypes' => $tag->getEntityTypeIris(),
            'category' => $tag->category !== null && trim($tag->category) !== '' ? trim($tag->category) : null,
        ];

        if ($tag->slug !== null && trim($tag->slug) !== '') {
            $payload['slug'] = trim($tag->slug);
        }

        return $this->request('PATCH', "tags/{$id}", Tag::class, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => $payload,
        ]);
    }

    public function delete(int $id): void
    {
        $this->request('DELETE', "tags/{$id}", null);
    }
}
