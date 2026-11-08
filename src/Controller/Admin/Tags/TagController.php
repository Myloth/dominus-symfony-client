<?php

namespace App\Controller\Admin\Tags;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/tags', name: 'admin_tags_')]
class TagController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('admin/tags/index.html.twig', [
            'page_title' => 'Gestion des Tags',
        ]);
    }
}
