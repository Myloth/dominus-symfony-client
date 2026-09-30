<?php

namespace App\Twig\Components;

use App\Client\Tags\TagClient;
use App\Dto\Tags\Tag;
use App\Dto\Tags\TagPaginatedResponse;
use App\Dto\Tags\TagSearch;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('tag_admin', template: 'components/tag_admin.html.twig')]
class TagAdminComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $searchName = '';

    #[LiveProp(writable: true)]
    public string $searchSlug = '';

    #[LiveProp(writable: true)]
    public string $searchEntityType = '';

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public int $itemsPerPage = 10;

    // Modal Form properties
    #[LiveProp(writable: true)]
    public bool $isModalOpen = false;

    #[LiveProp(writable: true)]
    public ?int $editingId = null;

    #[LiveProp]
    public string $editingSlug = '';

    #[LiveProp(writable: true)]
    public string $formName = '';

    #[LiveProp(writable: true)]
    public string $formEntityType = 'quest';

    #[LiveProp(writable: true)]
    public string $formCategory = '';

    #[LiveProp]
    public array $validationErrors = [];

    // Delete confirmation properties
    #[LiveProp(writable: true)]
    public bool $isDeleteConfirmOpen = false;

    #[LiveProp(writable: true)]
    public ?int $deletingId = null;

    #[LiveProp(writable: true)]
    public ?string $deletingName = null;

    // Notifications
    #[LiveProp]
    public ?string $flashSuccess = null;

    #[LiveProp]
    public ?string $flashError = null;

    public function __construct(
        private readonly TagClient $tagClient,
    ) {
    }

    public function updatedSearchName(): void
    {
        $this->page = 1;
    }

    public function updatedSearchSlug(): void
    {
        $this->page = 1;
    }

    public function updatedSearchEntityType(): void
    {
        $this->page = 1;
    }

    public function updatedItemsPerPage(): void
    {
        $this->page = 1;
    }

    public function getTags(): TagPaginatedResponse
    {
        $search = new TagSearch();
        $search->name = $this->searchName;
        $search->slug = $this->searchSlug;
        $search->entityType = $this->searchEntityType;
        $search->page = $this->page;
        $search->itemsPerPage = $this->itemsPerPage;

        try {
            return $this->tagClient->getPaginated($search);
        } catch (\Throwable $e) {
            return new TagPaginatedResponse([], 0, $this->page, $this->itemsPerPage);
        }
    }

    /**
     * @return array<string, string>
     */
    public function getAvailableEntityTypes(): array
    {
        return [
            'quest' => 'Quête (quest)',
            'pnj' => 'PNJ (pnj)',
            'event' => 'Événement (event)',
            'equipment' => 'Équipement (equipment)',
            'item' => 'Objet (item)',
            'dungeon' => 'Donjon (dungeon)',
            'guild' => 'Guilde (guild)',
            'player' => 'Joueur (player)',
        ];
    }

    #[LiveAction]
    public function openCreateModal(): void
    {
        $this->editingId = null;
        $this->editingSlug = '';
        $this->formName = '';
        $this->formEntityType = 'quest';
        $this->formCategory = '';
        $this->validationErrors = [];
        $this->flashError = null;
        $this->isModalOpen = true;
    }

    #[LiveAction]
    public function openEditModal(#[LiveArg] int $id): void
    {
        try {
            $tag = $this->tagClient->get($id);
            $this->editingId = $tag->id;
            $this->editingSlug = $tag->slug ?? '';
            $this->formName = $tag->name ?? '';
            $this->formEntityType = $tag->entityType ?? 'quest';
            $this->formCategory = $tag->category ?? '';
            $this->validationErrors = [];
            $this->flashError = null;
            $this->isModalOpen = true;
        } catch (\Throwable $e) {
            $this->flashError = 'Impossible de charger le tag sélectionné.';
        }
    }

    #[LiveAction]
    public function closeModal(): void
    {
        $this->isModalOpen = false;
        $this->validationErrors = [];
    }

    #[LiveAction]
    public function save(): void
    {
        $this->validationErrors = [];
        $this->flashSuccess = null;
        $this->flashError = null;

        $name = trim($this->formName);
        $entityType = trim($this->formEntityType);
        $category = trim($this->formCategory);

        if ($name === '') {
            $this->validationErrors['name'] = 'Le nom du tag est obligatoire.';
        }

        if ($entityType === '') {
            $this->validationErrors['entityType'] = 'Le type d\'entité est obligatoire.';
        }

        if (!empty($this->validationErrors)) {
            return;
        }

        $tag = new Tag();
        $tag->name = $name;
        $tag->entityType = $entityType;
        $tag->category = $category !== '' ? $category : null;

        try {
            if ($this->editingId !== null) {
                $tag->id = $this->editingId;
                $this->tagClient->update($this->editingId, $tag);
                $this->flashSuccess = sprintf('Le tag « %s » a été mis à jour avec succès.', $tag->name);
            } else {
                $this->tagClient->create($tag);
                $this->flashSuccess = sprintf('Le tag « %s » a été créé avec succès.', $tag->name);
            }

            $this->isModalOpen = false;
            $this->flashError = null;
        } catch (HttpException $e) {
            if ($e->getStatusCode() === 422) {
                $this->validationErrors['name'] = 'Une erreur de validation est survenue (ce nom de tag ou son slug existe peut-être déjà).';
            } else {
                $this->flashError = 'Une erreur est survenue lors de l\'enregistrement du tag.';
            }
        } catch (\Throwable $e) {
            $this->flashError = 'Une erreur inattendue est survenue : ' . $e->getMessage();
        }
    }

    #[LiveAction]
    public function confirmDelete(#[LiveArg] int $id, #[LiveArg] string $name): void
    {
        $this->deletingId = $id;
        $this->deletingName = $name;
        $this->isDeleteConfirmOpen = true;
    }

    #[LiveAction]
    public function cancelDelete(): void
    {
        $this->isDeleteConfirmOpen = false;
        $this->deletingId = null;
        $this->deletingName = null;
    }

    #[LiveAction]
    public function executeDelete(): void
    {
        if ($this->deletingId === null) {
            return;
        }

        $name = $this->deletingName ?? 'Le tag';

        try {
            $this->tagClient->delete($this->deletingId);
            $this->flashSuccess = sprintf('Le tag « %s » a été supprimé avec succès.', $name);
            $this->cancelDelete();
        } catch (\Throwable $e) {
            $this->flashError = sprintf('Erreur lors de la suppression de « %s » : %s', $name, $e->getMessage());
            $this->cancelDelete();
        }
    }

    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        $this->page = max(1, $page);
    }

    #[LiveAction]
    public function resetFilters(): void
    {
        $this->searchName = '';
        $this->searchSlug = '';
        $this->searchEntityType = '';
        $this->page = 1;
    }

    #[LiveAction]
    public function dismissFlash(): void
    {
        $this->flashSuccess = null;
        $this->flashError = null;
    }
}
