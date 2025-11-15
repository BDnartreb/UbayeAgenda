<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Event;
use App\Entity\Organisation;
use App\Form\EventType;
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

    #[Route('/organisation/event/add', name: 'organisation_event_add', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function add(Request $request): Response
    {
        $event = new Event();
        $organisation = $this->getUser();
        $event->setOrganisation($organisation);
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);  

        if ($form->isSubmitted() && $form->isValid())
        {
            $event->setOrganisation($organisation);
            $this->em->persist($event);
            $this->em->flush();

            $this->addFlash('success', 'Événement créé avec succès !');

            return $this->redirectToRoute('event', ['id' => $event->getId()]);
        }

        return $this->render('/organisation/eventForm.html.twig', ['form' => $form]);
    }

    // User on his own event
    // admin for all events
    #[Route('/organisation/event/update/{id}', name: 'organisation_event_update', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function update(int $id, Request $request): Response
    {
        $event = $this->em->getRepository(Event::class)->find($id);
        if(!$event){
            throw $this->createNotFoundException('Cet événement n\'existe pas.');
        }
        
        $organisation= $event->getOrganisation();
        $connectedUser = $this->getUser();
        
        $form = $this->createForm(EventType::class, $event)->handleRequest($request); 

        if ($organisation !== $connectedUser){
            throw $this->createAccessDeniedException();
        }

        if ($organisation === $connectedUser){
            if ($form->isSubmitted() && $form->isValid())
            {
                $this->em->persist($event);
                $this->em->flush();

                $this->addFlash('success', 'Événement modifié avec succès !');

                return $this->redirectToRoute('event', ['id' => $event->getId()]);
            }
        }   

        return $this->render('/organisation/eventForm.html.twig', ['form' => $form]);
    }

    // User for his own event
    // admin for all events
    #[Route('/organisation/event/delete/{id}', name: 'organisation_event_delete')]
    public function delete(int $id): Response
    {
        $event = $this->em->getRepository(Event::class)->find($id);
        if(!$event){
            throw $this->createNotFoundException('Cet événement n\'existe pas.');
        }
        $this->em->remove($event);
        $this->em->flush();

        $this->addFlash('success', 'Evénement supprimé avec succès !');

        return $this->redirectToRoute('organisation');
    }

//     if (file_exists($filePath)) {
//          unlink($filePath); // supprime physiquement le fichier sur le disque
//      }


    #[Route('/admin/event/update/{id}', name: 'admin_event_update')]
    public function admin_event_update(int $id, Request $request): Response
    {
        $event = $this->em->getRepository(Event::class)->find($id);
        if(!$event){
            throw $this->createNotFoundException('Cet événement n\'existe pas.');
        }
        
        $form = $this->createForm(EventType::class, $event)->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->em->persist($event);
            $this->em->flush();

            $this->addFlash('success', 'Événement modifié avec succès !');

            return $this->redirectToRoute('event', ['id' => $event->getId()]);
        }

        return $this->render('/organisation/eventForm.html.twig', ['form' => $form]);
    }

    #[Route('/admin/event/delete/{id}', name: 'admin_event_delete')]
    public function admin_event_delete(int $id): Response
    {
        $event = $this->em->getRepository(Event::class)->find($id);
        if(!$event){
            throw $this->createNotFoundException('Cet événement n\'existe pas.');
        }

        $this->em->remove($event);
        $this->em->flush();

        $this->addFlash('success', 'Evénement supprimé avec succès !');

        return $this->redirectToRoute('admin_events');
    }

    /**
     * Show list of events proposed by the organisation
     */
    #[Route('/organisation/events', name: 'organisation_events')]
    public function userEvents(): Response
    {
        /** @var Organisation $organisation */
        $organisation = $this->getUser();
        $events = $this->em->getRepository(Event::class)->findBy(['email' => $organisation->getEmail()]);

        return $this->render('organisation/organisation.html.twig', [
            'organisation' => $organisation,
            'events' => $events,
        ]);
    }

    /**
     * Show list of events for admin
     */
    #[Route('/admin/events', name: 'admin_events')]
    public function adminEvents(): Response
    {
        /** @var Organisation $organisation */
        $connectedUser = $this->getUser();
        $admin = $this->em->getRepository(Organisation::class)->findOneBy(['email' => 'admin@uabyeagenda.com']);

        if ($connectedUser === $admin){
            $events = $this->em->getRepository(Event::class)->findAll();

            return $this->render('admin/events.html.twig', [
                'events' => $events,
            ]);
        }

        return $this->redirectToRoute('/');
    }

  
}
