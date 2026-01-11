<?php

namespace App\Controller\Admin\Users;

use App\Client\Users\UserClient;
use App\Client\Users\GroupClient;
use App\Dto\Users\UserSearch;
use App\Form\Search\User\UserSearchType;
use App\Form\Edit\User\UserType;
use App\Controller\Admin\CrudController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('admin/user', name: 'admin_users_')]
class UserController extends CrudController
{
    public function __construct(
        SerializerInterface $serializer,
        private readonly GroupClient $groupClient,
        private readonly UserClient $userClient
    ) {
        parent::__construct($serializer);
    }

    #[Route('/list', name: 'list')]
    public function list(Request $request, UserClient $client)
    {
        $searchForm = $this->buildForm(UserSearchType::class, $this->generateFormOptions());

        return $this->render('admin/user/user/list.html.twig', ['searchForm' => $searchForm]);
    }

    #[Route('/load', name: 'load', options:['expose' => true])]
    public function load(UserClient $userClient, Request $request): JsonResponse
    {
        $searchParams = $this->initSearch($request, UserSearch::class);
        $users = $userClient->find($searchParams);

        return new JsonResponse([
            'data' => $this->renderData($users),
            'recordsTotal' => count($users),
            'recordsFiltered' => count($users)
            ]
        );

    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'], options: ["expose" => true])]
    public function new(Request $request): Response
    {
       $form = $this->buildForm(UserType::class, $this->generateFormOptions());

       $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userClient->create($form->getData());
            return new JsonResponse(['id' => $user->id]);
        }

        return $this->render(
            'admin/user/user/edit.html.twig',
            [
                'form' => $form->createView(),
            ]);
    }

    private function renderData(array $users)
    {
        dump($users);
        $results = [];
        /** @var User $user */
        foreach ($users as $user) {
            $line = [
                'id' => $user->id,
                'username' => $user->username,
                'groups' => implode(',', array_map(function ($value) {return $value?->name; }, $user?->groups)),
                'actions' => '',
            ];

            $results[] = $line;
        }

        return $results;
    }

    private function generateFormOptions(): array
    {
        $formOptions = ['groups' => []];
        $groups = $this->groupClient->getAll();
        array_walk($groups, function($value) use (&$formOptions) {
            $formOptions['groups'][$value->name] = $value->apiId;
        });
        
        return $formOptions;
    }
}