<?php

namespace App\Client\Tags;

use App\Client\AbstractClient;
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
        return $this->request('GET', 'tags', Tag::class . '[]');
    }

    public function get(int $id): Tag
    {
        return $this->request('GET', "tags/{$id}", Tag::class);
    }

    public function create(Tag $tag): Tag
    {
        $payload = [
            'name' => $tag->name,
            'entityType' => $tag->entityType,
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
            'entityType' => $tag->entityType,
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
