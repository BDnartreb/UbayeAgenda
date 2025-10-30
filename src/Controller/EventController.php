<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Event;
use App\Form\EventType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class EventController extends AbstractController
{
    // #[Route('/event/{id}', name: 'event')]
    // public function event(int $id): Response
    #[Route('/event', name: 'event')]
    public function event(): Response
    {
        return $this->render('event/event.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

    // User and admin
    #[Route('/event/add', name: 'event_add', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement créé avec succès !');

            return $this->redirectToRoute('event/event.html.twig');
        }

        return $this->render('event/eventForm.html.twig', ['form' => $form]);
    }

    // User on his own event
    // admin for all events
    #[Route('/event/update/{id}', name: 'event_update')]
    public function update(int $id): Response
    {
        return $this->render('event/event.html.twig', [
            'controler_name' => 'EventController',
        ]);
    }

    // User on his own event
    // admin for all events
    #[Route('/event/delete/{id}', name: 'event_delete')]
    public function delete(int $id): Response
    {
       return $this->render('/home.html.twig');
    }

    #[Route('/admin/events', name: 'events')]
    public function events(): Response
    {
        return $this->render('admin/events.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

  
}
