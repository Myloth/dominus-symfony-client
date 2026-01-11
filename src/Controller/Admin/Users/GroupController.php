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

    private function renderData(array $groups)
    {
        $results = [];
        /** @var Group $group */
        foreach ($groups as $group) {
            $line = [
                'id' => $group->id,
                'name' => $group->name,
                'roles' => implode(',', array_map(function ($role) { return $role->code; }, $group->roles)),
                'actions' => '',
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
