<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Organisation;
use App\Form\EventType;
use App\Form\LocationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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

    // #[Route('/organisation/event/add', name: 'organisation_event_add', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    // public function add(Request $request): Response
    // {
    //     $event = new Event();
    //     $organisation = $this->getUser();
    //     $event->setOrganisation($organisation);
    //     $form = $this->createForm(EventType::class, $event);
    //     $form->handleRequest($request);  
       
    //     if ($form->isSubmitted() && $form->isValid())
    //     {
    //         //$event->setOrganisation($organisation);

    //         $file = $event->getFile();
    //         if ($file != null) {
    //             $filename = md5(uniqid()) . '.' . $file->guessExtension();
    //             $event->setPoster('uploads/' . $filename);
    //             $event->getFile()->move('uploads/', $filename);
    //         }
           
    //         $this->em->persist($event);
    //         $this->em->flush();

    //         $this->addFlash('success', 'Événement créé avec succès !');

    //         return $this->redirectToRoute('event', ['id' => $event->getId()]);
    //     }

    //     return $this->render('/organisation/eventForm.html.twig', ['form' => $form]);
    // }







    #[Route('/organisation/event/add', name: 'organisation_event_add', methods: ['GET', 'POST'])]
public function add(Request $request, SessionInterface $session): Response
{
    $event = new Event();
    $organisation = $this->getUser();
    $event->setOrganisation($organisation);

    // Récupérer les données sauvegardées en session si elles existent
    $savedData = $session->get('event_form_data', []);
    if (!empty($savedData)) {
        $form = $this->createForm(EventType::class, $event);
        $form->submit($savedData); // pré-remplir le formulaire avec les données
    } else {
        $form = $this->createForm(EventType::class, $event);
    }

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion du fichier
        $file = $event->getFile();
        if ($file !== null) {
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $event->setPoster('uploads/' . $filename);
            $event->getFile()->move('uploads/', $filename);
        }

        $this->em->persist($event);
        $this->em->flush();

        // Nettoyer les données en session après enregistrement
        $session->remove('event_form_data');

        $this->addFlash('success', 'Événement créé avec succès !');
        return $this->redirectToRoute('event', ['id' => $event->getId()]);
    }

    // Si l'utilisateur clique sur "Créer un nouveau lieu", sauvegarder les données dans la session
    if ($request->query->get('save_session') === '1') {
        $session->set('event_form_data', $request->request->get('event'));
        return $this->redirectToRoute('organisation_location_add');
    }

    return $this->render('/organisation/eventForm.html.twig', [
        'form' => $form,
    ]);
}







    #[Route('/organisation/location/add', name: 'organisation_location_add', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function organisationLocationAdd(Request $request): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);  

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->em->persist($location);
            $this->em->flush();

            $this->addFlash('success', 'Lieu créé avec succès !');

            return $this->redirectToRoute('organisation_event_add');
        }

        return $this->render('/organisation/locationForm.html.twig', ['form' => $form]);
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
                $file = $event->getFile();
                if ($file != null) {
                    $filename = md5(uniqid()) . '.' . $file->guessExtension();
                    $event->setPoster('uploads/' . $filename);
                    $event->getFile()->move('uploads/', $filename);
                }
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
