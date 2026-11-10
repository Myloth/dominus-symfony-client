<?php

namespace App\Twig\Components;

use App\Client\Tags\TagClient;
use App\Dto\Tags\EntityType;
use App\Dto\Tags\Tag;
use App\Dto\Tags\TagForm;
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

    #[LiveProp(
        writable: ['name', 'slug', 'entityType', 'page', 'itemsPerPage'],
        useSerializerForHydration: true,
        onUpdated: [
            'name' => 'resetPage',
            'slug' => 'resetPage',
            'entityType' => 'resetPage',
            'itemsPerPage' => 'resetPage',
        ],
    )]
    public TagSearch $search;

    #[LiveProp(
        writable: ['name', 'entityTypes', 'category'],
        useSerializerForHydration: true,
    )]
    public TagForm $form;

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
    public ?array $deletingTag = null;

    /**
     * @var array{type: string, message: string}|null
     */
    #[LiveProp]
    public ?array $flash = null;

    public function __construct(
        private readonly TagClient $tagClient,
    ) {
        $this->search = new TagSearch();
        $this->form = new TagForm();
    }

    public function resetPage(): void
    {
        $this->search->page = 1;
    }

    public function hasActiveFilters(): bool
    {
        return $this->search->hasCustomFilters();
    }

    public function getTags(): TagPaginatedResponse
    {
        try {
            return $this->tagClient->getPaginated($this->search);
        } catch (\Throwable) {
            return new TagPaginatedResponse([], 0, $this->search->page, $this->search->itemsPerPage);
        }
    }

    /**
     * @return list<EntityType>
     */
    public function getAvailableEntityTypes(): array
    {
        try {
            return $this->tagClient->getEntityTypes();
        } catch (\Throwable) {
            return [];
        }
    }

    #[LiveAction]
    public function openCreateModal(): void
    {
        $this->form = new TagForm();
        $this->validationErrors = [];
        $this->flash = null;
        $this->isModalOpen = true;
    }

    #[LiveAction]
    public function openEditModal(#[LiveArg] int $id): void
    {
        try {
            $tag = $this->tagClient->get($id);
            $this->form = TagForm::fromTag($tag);
            $this->validationErrors = [];
            $this->flash = null;
            $this->isModalOpen = true;
        } catch (\Throwable) {
            $this->flash = [
                'type' => 'danger',
                'message' => 'Impossible de charger le tag sélectionné.',
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
    public function save(): void
    {
        $this->validationErrors = [];
        $this->flash = null;

        $name = trim($this->form->name);
        $entityTypes = array_values(array_filter(
            $this->form->entityTypes,
            static fn (string $iri): bool => trim($iri) !== ''
        ));
        $category = trim($this->form->category);

        if ($name === '') {
            $this->validationErrors['name'] = 'Le nom du tag est obligatoire.';
        }

        if (empty($entityTypes)) {
            $this->validationErrors['entityTypes'] = 'Veuillez sélectionner au moins un type d\'entité.';
        }

        if (!empty($this->validationErrors)) {
            return;
        }

        $tag = new Tag();
        $tag->name = $name;
        $tag->entityTypes = $entityTypes;
        $tag->category = $category !== '' ? $category : null;

        try {
            if ($this->form->id !== null) {
                $tag->id = $this->form->id;
                $this->tagClient->update($this->form->id, $tag);
                $this->flash = [
                    'type' => 'success',
                    'message' => sprintf('Le tag « %s » a été mis à jour avec succès.', $tag->name),
                ];
            } else {
                $this->tagClient->create($tag);
                $this->flash = [
                    'type' => 'success',
                    'message' => sprintf('Le tag « %s » a été créé avec succès.', $tag->name),
                ];
            }

            $this->isModalOpen = false;
        } catch (HttpException $e) {
            if ($e->getStatusCode() === 422) {
                $this->validationErrors['name'] = 'Une erreur de validation est survenue (ce nom de tag ou son slug existe peut-être déjà).';
            } else {
                $this->flash = [
                    'type' => 'danger',
                    'message' => 'Une erreur est survenue lors de l\'enregistrement du tag.',
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
        $this->deletingTag = [
            'id' => $id,
            'name' => $name,
        ];
    }

    #[LiveAction]
    public function cancelDelete(): void
    {
        $this->deletingTag = null;
    }

    #[LiveAction]
    public function executeDelete(): void
    {
        if ($this->deletingTag === null) {
            return;
        }

        $id = $this->deletingTag['id'];
        $name = $this->deletingTag['name'];

        try {
            $this->tagClient->delete($id);
            $this->flash = [
                'type' => 'success',
                'message' => sprintf('Le tag « %s » a été supprimé avec succès.', $name),
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
        $this->search = new TagSearch();
    }

    #[LiveAction]
    public function dismissFlash(): void
    {
        $this->flash = null;
    }
}
