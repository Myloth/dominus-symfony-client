<?php

namespace App\Controller\Admin\Events;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/events', name: 'admin_events_')]
class EventController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('admin/events/index.html.twig', [
            'page_title' => 'Gestion des Événements',
        ]);
    }
}
