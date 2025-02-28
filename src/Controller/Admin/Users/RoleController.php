<?php

namespace App\Controller\Admin\Users;

use App\Client\Users\RoleClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/user/role', name: 'admin_users_role_')]
class RoleController extends AbstractController
{
    #[Route('/list', name: 'list')]
    public function list(RoleClient $roleClient)
    {
        $roles = $roleClient->getAll();

        return $this->render('admin/user/role/list.html.twig', ['roles' => $roles]);
    }

}
