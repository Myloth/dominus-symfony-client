<?php

namespace App\Controller\Admin\Users;

use App\Client\Users\GroupClient;
use App\Client\Users\RoleClient;
use App\Dto\Users\Group;
use App\Form\Edit\User\GroupType;
use App\Controller\Admin\CrudController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\Search\User\GroupSearchType;
use App\Dto\Users\GroupSearch;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class GroupController
 */
#[AsController]
#[Route('/admin/user/group', name: 'admin_users_group_')]
class GroupController extends CrudController
{
    public function __construct(
        SerializerInterface $serializer,
        private readonly GroupClient $groupClient,
        private readonly RoleClient $roleClient
    ) {
        parent::__construct($serializer);
    }

    #[Route('/list', name: 'list')]
    public function list(GroupClient $groupClient)
    {
        $searchForm = $this->buildForm(GroupSearchType::class, $this->generateFormOptions());

        return $this->render('admin/user/group/list.html.twig', ['searchForm' => $searchForm]);
    }

    #[Route('/load', name: 'load', options:['expose' => true])]
    public function load(GroupClient $groupClient, Request $request): JsonResponse
    {
        $searchParams = $this->initSearch($request, GroupSearch::class);

        $groups = $this->groupClient->find($searchParams);

        return new JsonResponse([
            'data' => $this->renderData($groups),
            'recordsTotal' => count($groups),
            'recordsFiltered' => count($groups)
            ]
        );

    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'], options: ["expose" => true])]
    public function new(Request $request): Response
    {
        $form = $this->buildForm(GroupType::class, $this->generateFormOptions());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $group = $this->groupClient->create($form->getData());

            return new JsonResponse(['id' => $group->id]);
        }

        return $this->render(
            'admin/user/group/edit.html.twig',
            [
                'form' => $form->createView(),
            ]);
    }

    private function renderData(array $groups): array
    {
        $results = [];
        /** @var Group $group */
        foreach ($groups as $group) {
            $rolesHtml = '';
            foreach ($group->roles ?? [] as $role) {
                if ($role?->code) {
                    $isAdmin = str_contains($role->code, 'ADMIN');
                    $badgeClass = $isAdmin ? 'role-badge role-badge-admin' : 'role-badge';
                    $icon = $isAdmin ? 'fa-shield-alt' : 'fa-user-tag';
                    $rolesHtml .= '<span class="' . $badgeClass . '"><i class="fas ' . $icon . '"></i> ' . htmlspecialchars($role->code) . '</span> ';
                }
            }
            if ($rolesHtml === '') {
                $rolesHtml = '<span class="text-muted">-</span>';
            }

            $actionsHtml = '<div class="col-actions text-right">'
                . '<a href="' . $this->generateUrl('admin_users_group_new') . '" class="action-btn action-edit" title="Modifier"><i class="fas fa-pen"></i> Modifier</a>'
                . '</div>';

            $line = [
                'id' => '<code class="tag-slug-pill">#' . $group->id . '</code>',
                'name' => '<span class="col-name"><strong>' . htmlspecialchars($group->name ?? '') . '</strong></span>',
                'roles' => $rolesHtml,
                'actions' => $actionsHtml,
            ];

            $results[] = $line;
        }

        return $results;
    }

    private function generateFormOptions(): array
    {
        $formOptions = ['roles' => []];
        $roles = $this->roleClient->getAll();
        array_walk($roles, function($value) use (&$formOptions) {
            $formOptions['roles'][$value->apiId] = $value->code;
        });
        
        return $formOptions;
    }
}
