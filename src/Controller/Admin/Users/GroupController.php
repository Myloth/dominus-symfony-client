<?php

namespace App\Controller\Admin\Users;


use App\Client\Users\GroupClient;
use App\Client\Users\RoleClient;
use App\Dto\Users\Group;
use App\Form\Edit\User\GroupType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\Search\User\GroupSearchType;
/**
 * Class GroupController
 */
#[AsController]
#[Route('/admin/user/group', name: 'admin_users_group_')]
class GroupController extends AbstractController
{
    public function __construct(
        private readonly GroupClient $groupClient,
        private readonly RoleClient $roleClient
    ) {

    }


    #[Route('/list')]
    public function list(GroupClient $groupClient)
    {
        $searchForm = $this->buildSearchForm();
        $groups = $this->groupClient->getAll();

        return $this->render('admin/user/groups/list.html.twig', ['groups' => $groups, 'searchForm' => $searchForm]);
    }

    #[Route('/load', name: 'load', options:['expose' => true])]
    public function load(GroupClient $groupClient, Request $request): JsonResponse
    {
        $groups = $this->groupClient->getAll();
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
        $form = $this->buildEditForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            dump('here');
            $group = $this->groupClient->create($form->getData());

            return new JsonResponse(['id' => $group->id]);
        }

        return $this->render(
            'admin/user/groups/edit.html.twig',
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

    private function buildEditForm(?Group $group = null): FormInterface
    {
        $formOptions = ['roles' => []];
        $roles = $this->roleClient->getAll();
        array_walk($roles, function($value) use (&$formOptions) {
            $formOptions['roles'][$value->apiId] = $value->code;
        });

        return $this->createForm(GroupType::class, $group, $formOptions);
    }

    private function buildSearchForm(): FormInterface
    {
        $formOptions = ['roles' => []];
        $roles = $this->roleClient->getAll();
        array_walk($roles, function($value) use (&$formOptions) {
            $formOptions['roles'][$value->apiId] = $value->code;
        });
        
        return $this->createForm(GroupSearchType::class, null, $formOptions);
    }
}
