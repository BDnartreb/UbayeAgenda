<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class EventController extends AbstractController
{

    protected EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em,
    )
    {
        $this->em = $em;
    }


    #[Route('/event/event/{id}', name: 'event')]
    public function event(Event $event): Response
    {
        return $this->render('event/event.html.twig', [
            'event' => $event,
        ]);
    }

    // User and admin
    #[Route('/organisation/event/add', name: 'event_add', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event)->handleRequest($request);

        $organisation = $this->getUser();

        if ($form->isSubmitted() && $form->isValid())
        {
            $event->setOrganisation($organisation);
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement créé avec succès !');

            return $this->redirectToRoute('event', ['id' => $event->getId()]);
        }

        return $this->render('organisation/eventForm.html.twig', ['form' => $form]);
    }

    // User on his own event
    // admin for all events
    #[Route('/organisation/event/update/{id}', name: 'event_update')]
    public function update(int $id, EventRepository $eventRepository, Request $request, EntityManagerInterface $em): Response
    {
        $event = $eventRepository->find($id);
        //dd($event);
        $form = $this->createForm(EventType::class, $event)->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement modifié avec succès !');

            return $this->redirectToRoute('event', ['id' => $event->getId()]);
        }

        return $this->render('user/eventForm.html.twig', ['form' => $form]);

    }

    // User for his own event
    // admin for all events
    #[Route('/organisation/event/delete/{id}', name: 'event_delete')]
    public function delete(int $id, EventRepository $eventRepository, EntityManagerInterface $em): Response
    {
        $event = $eventRepository->find($id);
        $em->remove($event);
        $em->flush();

        $this->addFlash('success', 'Evénement supprimé avec succès !');
        return $this->redirectToRoute('home');
    }

    /**
     * Show list of events proposed by one or several organisations the user is member of
     */
    #[Route('/organisation/events', name: 'organisation_events')]
    public function userEvents(): Response
    {
        return $this->render('organisation/organisation_events.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

    /**
     * Show list of events
     */
    #[Route('/admin/events', name: 'admin_events')]
    public function adminEvents(): Response
    {
        return $this->render('admin/events.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

  
}
