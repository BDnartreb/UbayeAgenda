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
use Symfony\Component\Form\FormError;

use function PHPUnit\Framework\throwException;

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

    #[Route('/organisation/event/add', name: 'organisation_event_add', methods: ['GET', 'POST'])]
    public function add(Request $request, SessionInterface $session): Response
    {
        $event = new Event();
        /** @var \App\Entity\Organisation|null $organisation */
        $organisation = $this->getUser();
        if(!$organisation instanceof Organisation){
            throw $this->createNotFoundException('Admin introuvable!');
        }

        $event->setOrganisation($organisation);

        // Récupérer les données sauvegardées en session si elles existent
        $savedData = $session->get('event_form_data', []);
        if (!empty($savedData)) {
            // $form = $this->createForm(EventType::class, $event);
            $form = $this->createForm(EventType::class, $event, ['organisation' => $this->getUser(),]);
            $form->submit($savedData); // pré-remplir le formulaire avec les données
        } else {
            $form = $this->createForm(EventType::class, $event, ['organisation' => $this->getUser(),]);
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

            $startDate = $event->getStartDate();
            $endTime = $event->getEndDate();

            if ($startDate && $endTime) {

                $endDate = (clone $startDate)
                    ->setTime(
                        (int) $endTime->format('H'),
                        (int) $endTime->format('i')
                    );

                $event->setEndDate($endDate);
  
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
            $location->setOrganisation($this->getUser());

            $this->em->persist($location);
            $this->em->flush();

            $this->addFlash('success', 'Lieu créé avec succès !');

            return $this->redirectToRoute('organisation_event_add');
        }

        return $this->render('/organisation/locationForm.html.twig', ['form' => $form]);
    }

    #[Route('/organisation/location/update/{id}', name: 'organisation_location_update', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function organisationLocationUpdate(int $id, Request $request): Response
    {
        $location = $this->em->getRepository(Location::class)->find($id);
        if(!$location){
            throw $this->createNotFoundException('Ce lieu n\'existe pas.');
        }

        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);  

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->em->persist($location);
            $this->em->flush();

            $this->addFlash('success', 'Lieu modifié avec succès !');

            return $this->redirectToRoute('organisation_event_add');
        }

        return $this->render('/organisation/locationForm.html.twig', ['form' => $form]);
    }

    // Pb delete impossible avec lien avec event
    #[Route('/admin/location/delete/{id}', name: 'admin_location_delete', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function organisationLocationDelete(int $id, Request $request): Response
    {
        $location = $this->em->getRepository(Location::class)->find($id);
        if(!$location){
            throw $this->createNotFoundException('Ce lieu n\'existe pas.');
        }

        $this->em->remove($location);
        $this->em->flush();

        $this->addFlash('success', 'Lieu supprimé avec succès !');

        return $this->redirectToRoute('organisation_event_add');
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
        
        $admin = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $_ENV['ADMIN_EMAIL']]);
        $organisation= $event->getOrganisation();
        $connectedUser = $this->getUser();
        
        $form = $this->createForm(EventType::class, $event)->handleRequest($request); 

        if ($organisation !== $connectedUser && $connectedUser !== $admin){
             throw $this->createAccessDeniedException();
        }

        if ($organisation === $connectedUser || $connectedUser === $admin){
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

    /**
     * Show list of events for admin
     */
    #[Route('/admin/events', name: 'admin_events')]
    public function adminEvents(Request $request): Response
    {
        /** @var Organisation $connectedUser */
        $connectedUser = $this->getUser();
        $admin = $this->em->getRepository(Organisation::class)->findOneBy(['email' => 'admin@ubayeagenda.com']);

        if ($connectedUser === $admin){
            $organisations = $this->em->getrepository(Organisation::class)->findAll();
            $selectedOrganisations = $request->query->all('organisations');
            $selectedOrganisationsId = [];
            foreach ($selectedOrganisations as $orgaName) {
                $organisationId = $this->em->getRepository(Organisation::class)->findOneBy(['name' => $orgaName])->getId();
                $selectedOrganisationsId[] = $organisationId;
            }

            $selectedDate = $request->query->get('selectedDate');
            if(!$selectedDate){
                $today = new \DateTime('today');
                $selectedDate = $today->format('Y-m-d');
            }

            // $events = $this->em->getRepository(Event::class)
            // ->findEventsOrderedByStartDateFromSelectedDate(
            //     organisations: $selectedOrganisationsId,
            //     date: $selectedDate
            // );

            $events = $this->em->getRepository(Event::class)
            ->findEventsOrderedByStartDate(
                organisations: $selectedOrganisationsId,
                date: $selectedDate
            );

            return $this->render('admin/events.html.twig', [
                'events' => $events,
                'organisations' => $organisations,
                'selectedOrganisations' => $selectedOrganisations,
                'selectedDate' => $selectedDate,
            ]);
        }

        return $this->redirectToRoute('/');
    }
}
