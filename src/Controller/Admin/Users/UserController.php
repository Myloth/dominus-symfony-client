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
use App\Datatables\UserDatatable;

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
    public function list()
    {
        return $this->render('admin/user/user/list.html.twig');
    }

    #[Route('/load', name: 'load', options:['expose' => true])]
    public function load(UserDatatable $table, Request $request): JsonResponse
    {
        if ($request->isMethod('POST')) {
            $request->query->add($request->request->all());
        }

        $table->handleRequest($request);

        return $table->getResponse();
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