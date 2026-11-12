<?php

namespace App\Dto\Events;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class Event
{
    #[SerializedName('@id')]
    public ?string $apiId = null;

    public ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    public ?string $name = null;

    public ?\DateTimeImmutable $startDate = null;

    public ?\DateTimeImmutable $endDate = null;

    public bool $active = false;

    public ?string $description = null;

    public ?\DateTimeImmutable $createdAt = null;

    public array $tags = [];

    public array $quests = [];

    public function getFormattedStartDate(): string
    {
        return $this->startDate?->format('d/m/Y H:i') ?? '-';
    }

    public function getFormattedEndDate(): string
    {
        return $this->endDate?->format('d/m/Y H:i') ?? '-';
    }

    public function getFormattedCreatedAt(): string
    {
        return $this->createdAt?->format('d/m/Y H:i') ?? '-';
    }

    public function getStartDateForInput(): string
    {
        return $this->startDate?->format('Y-m-d\TH:i') ?? '';
    }

    public function getEndDateForInput(): string
    {
        return $this->endDate?->format('Y-m-d\TH:i') ?? '';
    }

    public function getShortDescription(int $maxLength = 80): string
    {
        if ($this->description === null || trim($this->description) === '') {
            return '-';
        }

        $text = trim($this->description);

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        return mb_substr($text, 0, $maxLength) . '…';
    }

    /**
     * @return list<string>
     */
    public function getTagIris(): array
    {
        $iris = [];

        foreach ($this->tags as $tag) {
            if (is_string($tag) && trim($tag) !== '') {
                $iris[] = trim($tag);
            } elseif (is_array($tag)) {
                if (isset($tag['@id']) && is_string($tag['@id'])) {
                    $iris[] = $tag['@id'];
                } elseif (isset($tag['id'])) {
                    $iris[] = '/tags/' . $tag['id'];
                }
            } elseif (is_object($tag)) {
                if (property_exists($tag, 'apiId') && is_string($tag->apiId) && $tag->apiId !== '') {
                    $iris[] = $tag->apiId;
                } elseif (property_exists($tag, 'id') && $tag->id !== null) {
                    $iris[] = '/tags/' . $tag->id;
                }
            }
        }

        return array_values(array_unique($iris));
    }
}
