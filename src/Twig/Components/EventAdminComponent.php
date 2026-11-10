<?php

namespace App\Twig\Components;

use App\Client\Events\EventClient;
use App\Client\Tags\TagClient;
use App\Dto\Events\Event;
use App\Dto\Events\EventForm;
use App\Dto\Events\EventPaginatedResponse;
use App\Dto\Events\EventSearch;
use App\Dto\Tags\Tag;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('event_admin', template: 'components/event_admin.html.twig')]
class EventAdminComponent
{
    use DefaultActionTrait;

    #[LiveProp(
        writable: ['name', 'active', 'period', 'startDate', 'endDate', 'page', 'itemsPerPage'],
        useSerializerForHydration: true,
        onUpdated: [
            'name' => 'resetPage',
            'active' => 'resetPage',
            'period' => 'resetPage',
            'startDate' => 'resetPage',
            'endDate' => 'resetPage',
            'itemsPerPage' => 'resetPage',
        ],
    )]
    public EventSearch $search;

    #[LiveProp(
        writable: ['name', 'startDate', 'endDate', 'active', 'description', 'tags'],
        useSerializerForHydration: true,
    )]
    public EventForm $form;

    #[LiveProp(writable: true)]
    public bool $isModalOpen = false;

    /**
     * @var array<string, string>
     */
    #[LiveProp]
    public array $validationErrors = [];

    /**
     * @var array{id: int, name: string}|null
     */
    #[LiveProp]
    public ?array $deletingEvent = null;

    /**
     * @var array{type: string, message: string}|null
     */
    #[LiveProp]
    public ?array $flash = null;

    public function __construct(
        private readonly EventClient $eventClient,
        private readonly TagClient $tagClient,
    ) {
        $this->search = new EventSearch();
        $this->form = new EventForm();
    }

    public function resetPage(): void
    {
        $this->search->page = 1;
    }

    public function hasActiveFilters(): bool
    {
        return $this->search->hasCustomFilters();
    }

    public function getEvents(): EventPaginatedResponse
    {
        try {
            return $this->eventClient->getPaginated($this->search);
        } catch (\Throwable) {
            return new EventPaginatedResponse([], 0, $this->search->page, $this->search->itemsPerPage);
        }
    }

    /**
     * @return list<Tag>
     */
    public function getEventTags(): array
    {
        try {
            return $this->tagClient->getByEntityType('event');
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, Tag>
     */
    public function getEventTagsMap(): array
    {
        $map = [];
        foreach ($this->getEventTags() as $tag) {
            $iri = $tag->apiId ?? ($tag->id !== null ? '/tags/' . $tag->id : null);
            if ($iri !== null) {
                $map[$iri] = $tag;
            }
        }

        return $map;
    }

    /**
     * @return array<string, string>
     */
    public function getAvailablePeriods(): array
    {
        return [
            'ongoing' => 'En cours',
            'upcoming' => 'À venir',
            'past' => 'Terminés',
        ];
    }

    #[LiveAction]
    public function openCreateModal(): void
    {
        $this->form = new EventForm();
        $this->validationErrors = [];
        $this->flash = null;
        $this->isModalOpen = true;
    }

    #[LiveAction]
    public function openEditModal(#[LiveArg] int $id): void
    {
        try {
            $event = $this->eventClient->get($id);
            $this->form = EventForm::fromEvent($event);
            $this->validationErrors = [];
            $this->flash = null;
            $this->isModalOpen = true;
        } catch (\Throwable) {
            $this->flash = [
                'type' => 'danger',
                'message' => 'Impossible de charger l\'événement sélectionné.',
            ];
        }
    }

    #[LiveAction]
    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->validationErrors = [];
    }

    #[LiveAction]
    public function toggleActive(#[LiveArg] int $id, #[LiveArg] bool $currentState): void
    {
        $this->flash = null;

        try {
            $updated = $this->eventClient->updateActive($id, !$currentState);
            $statusLabel = $updated->active ? 'activé' : 'désactivé';
            $this->flash = [
                'type' => 'success',
                'message' => sprintf('L\'événement « %s » a été %s.', $updated->name ?? (string) $id, $statusLabel),
            ];
        } catch (\Throwable $e) {
            $this->flash = [
                'type' => 'danger',
                'message' => 'Impossible de modifier le statut de l\'événement : ' . $e->getMessage(),
            ];
        }
    }

    #[LiveAction]
    public function save(): void
    {
        $this->validationErrors = [];
        $this->flash = null;

        $name = trim($this->form->name);
        $startDateStr = trim($this->form->startDate);
        $endDateStr = trim($this->form->endDate);
        $description = trim($this->form->description);

        if ($name === '') {
            $this->validationErrors['name'] = 'Le nom de l\'événement est obligatoire.';
        }

        $startDate = null;
        if ($startDateStr !== '') {
            try {
                $startDate = new \DateTimeImmutable($startDateStr);
            } catch (\Throwable) {
                $this->validationErrors['startDate'] = 'Format de date de début invalide.';
            }
        }

        $endDate = null;
        if ($endDateStr !== '') {
            try {
                $endDate = new \DateTimeImmutable($endDateStr);
            } catch (\Throwable) {
                $this->validationErrors['endDate'] = 'Format de date de fin invalide.';
            }
        }

        if ($startDate !== null && $endDate !== null && $endDate < $startDate) {
            $this->validationErrors['endDate'] = 'La date de fin doit être postérieure ou égale à la date de début.';
        }

        if (!empty($this->validationErrors)) {
            return;
        }

        $event = new Event();
        $event->name = $name;
        $event->startDate = $startDate;
        $event->endDate = $endDate;
        $event->active = $this->form->active;
        $event->description = $description !== '' ? $description : null;
        $event->tags = array_values(array_filter(
            $this->form->tags,
            static fn (string $iri): bool => trim($iri) !== ''
        ));

        try {
            if ($this->form->id !== null) {
                $event->id = $this->form->id;
                $this->eventClient->update($this->form->id, $event);
                $this->flash = [
                    'type' => 'success',
                    'message' => sprintf('L\'événement « %s » a été mis à jour avec succès.', $event->name),
                ];
            } else {
                $this->eventClient->create($event);
                $this->flash = [
                    'type' => 'success',
                    'message' => sprintf('L\'événement « %s » a été créé avec succès.', $event->name),
                ];
            }

            $this->isModalOpen = false;
        } catch (HttpException $e) {
            if ($e->getStatusCode() === 422) {
                $this->validationErrors['name'] = 'Une erreur de validation est survenue. Vérifiez le nom et la cohérence des dates.';
            } else {
                $this->flash = [
                    'type' => 'danger',
                    'message' => 'Une erreur est survenue lors de l\'enregistrement de l\'événement.',
                ];
            }
        } catch (\Throwable $e) {
            $this->flash = [
                'type' => 'danger',
                'message' => 'Une erreur inattendue est survenue : ' . $e->getMessage(),
            ];
        }
    }

    #[LiveAction]
    public function confirmDelete(#[LiveArg] int $id, #[LiveArg] string $name): void
    {
        $this->deletingEvent = [
            'id' => $id,
            'name' => $name,
        ];
    }

    #[LiveAction]
    public function cancelDelete(): void
    {
        $this->deletingEvent = null;
    }

    #[LiveAction]
    public function executeDelete(): void
    {
        if ($this->deletingEvent === null) {
            return;
        }

        $id = $this->deletingEvent['id'];
        $name = $this->deletingEvent['name'];

        try {
            $this->eventClient->delete($id);
            $this->flash = [
                'type' => 'success',
                'message' => sprintf('L\'événement « %s » a été supprimé avec succès.', $name),
            ];
            $this->cancelDelete();
        } catch (\Throwable $e) {
            $this->flash = [
                'type' => 'danger',
                'message' => sprintf('Erreur lors de la suppression de « %s » : %s', $name, $e->getMessage()),
            ];
            $this->cancelDelete();
        }
    }

    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        $this->search->page = max(1, $page);
    }

    #[LiveAction]
    public function resetFilters(): void
    {
        $this->search = new EventSearch();
    }

    #[LiveAction]
    public function dismissFlash(): void
    {
        $this->flash = null;
    }
}
