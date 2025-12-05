<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Organisation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\RegisterType;
use App\Form\LocationType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


final class OrganisationController extends AbstractController
{
    protected EntityManagerInterface $em;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $userPasswordHasher,
        )
    {
        $this->em = $em;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    #[Route('/register', name: 'register', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function register(Request $request): Response
    {
        $organisation = new Organisation();

        $form = $this->createForm(RegisterType::class, $organisation)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword !== null && $plainPassword !== '') {
                $organisation->setPassword($this->userPasswordHasher->hashPassword($organisation, $plainPassword));
            }         
            $organisation->setRoles(['ROLE_ORGANISATION']);

            $this->em->persist($organisation);
            $this->em->flush();
            
            $this->addFlash('success', 'Inscription réussie. Vous pouvez vous connecter !');
            
            return $this->redirectToRoute('logout');
        }

        return $this->render('home/register.html.twig', [
            'form' => $form,
            'buttonName' => 'S\'inscrire',
        ]);
    }

    #[Route('/organisation/update/{id?}', name: 'organisation_update', methods: ['GET', 'POST'])]
    public function update(Request $request, ?int $id): Response
    {
        /** @var Organisation|null $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser){
            throw $this->createNotFoundException('Organisation introuvable!');
        }

        if (in_array('ROLE_ADMIN', $currentUser->getRoles(), true) && $id !== null) {
            if (!$id){
                throw $this->createNotFoundException('id manquant!');
            }
            $organisation = $this->em->getRepository(Organisation::class)->find($id);
            if (!$organisation){
                throw $this->createNotFoundException('Organisation introuvable avec cet id!');
            }
        } else {
            $organisation = $currentUser;
        }

        $form = $this->createForm(RegisterType::class, $organisation)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $plainPassword = $form->get('plainPassword')->getData();
            if(!empty($plainPassword)){
                $organisation->setPassword($this->userPasswordHasher->hashPassword($organisation, $plainPassword));
            }
            $this->em->persist($organisation);
            $this->em->flush();

            return $this->redirectToRoute('organisation');
        }

        return $this->render('home/register.html.twig', [
            'form' => $form->createView(),
            'buttonName' => 'Modifier',
        ]);
    }

    #[Route('/organisation/delete/{id?}', name: 'organisation_delete', methods: ['POST'])]
    public function delete(
        ?int $id,
        Request $request,
        TokenStorageInterface $tokenStorage
        ): Response
    {
        /** @var Organisation|null $currentUser */
        $currentUser = $this->getUser(); 

        if (!$currentUser){
            throw $this->createNotFoundException('Il n\'y a pas d\'utilisateur connecté!');
        }

        if (in_array('ROLE_ADMIN', $currentUser->getRoles(), true) && $id !== null) {
            if (!$id){
                throw $this->createNotFoundException('id manquant!');
            }
            $organisation = $this->em->getRepository(Organisation::class)->find($id);
            if (!$organisation){
                throw $this->createNotFoundException('Organisation introuvable avec cet id!');
            }
            if (in_array('ROLE_ADMIN', $organisation->getRoles(), true)){
                throw $this->createNotFoundException('Il n\'est pas permis de supprimer le compte Administrateur!');
            }
        } else {
            $organisation = $currentUser;
        }   

        // Check CSRF token
        if (!$this->isCsrfTokenValid('delete'.$organisation->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('home');
        }

        // Logout user if connected
        if ($currentUser === $organisation) {
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();
        }

        $this->em->remove($organisation);
        $this->em->flush();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        
        return $this->redirectToRoute('home');
    }

    /**
     * show organisation page
     */
    #[Route('/organisation', name: 'organisation')]
    public function organisation(Request $request): Response
    {
        /** @var \App\Entity\Organisation|null $organisation */
        $organisation = $this->getUser();
        $organisationId[] = $organisation->getId();
        $selectedDate = $request->query->get('selectedDate');
        //dd($selectedDate);
        if(!$selectedDate){
            $today = new \DateTime('today');
            $selectedDate = $today->format('Y-m-d');
        }
        //$events = $this->em->getRepository(Event::class)->findEventsByOrganisationOrderedByStartDateFromToday($organisationId);
        $events = $this->em->getRepository(Event::class)
            ->findEventsOrderedByStartDate(
                date: $selectedDate,
                organisations: $organisationId
            );
        return $this->render('organisation/organisation.html.twig', [
            'organisation' => $organisation,
            'selectedDate' => $selectedDate,
            'events' => $events,
        ]);
    }

    /**
     * show list of organisations
     */
    #[Route('/admin/organisations', name: 'admin_organisations')]
    public function organisations(): Response
    {
        /** @var Organisation|null $currentUser */
        $currentUser = $this->getUser();
        $admin = $this->em->getRepository(Organisation::class)->findOneBy(['email' => 'admin@ubayeagenda.com']);

        if ($currentUser === $admin){
            $organisations = $this->em->getRepository(Organisation::class)->findByRole('ROLE_ORGANISATION');

            return $this->render('admin/organisations.html.twig', [
                'organisations' => $organisations,
            ]);
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/organisation/calendar', name: 'organisation_calendar')]
    public function agenda(): Response
    {
        // $events = $this->em->getRepository(Event::class)->findAll();
        $period = 365;
        $events = $this->em->getRepository(Event::class)->findEventsOrderedByStartDate(date: 'today', period: $period);
        
        $eventsArray = [];

        foreach ($events as $event) {
            $eventsArray[] = [
                'title' => $event->getName(),
                'start' => $event->getStartDate()->format('Y-m-d\TH:i:s'),
                'end'   => $event->getEndDate() ? $event->getEndDate()->format('Y-m-d\TH:i:s') : null,
                'color' => '#3788d8', // optionnel
                // specific field
                'extendedProps' => [
                    'organisation' => $event->getOrganisation()->getName(),
                    'location' => $event->getLocation()->getName(),
                    'town' => $event->getLocation()->getTown(),
                ],
            ];
        }

        return $this->render('/organisation/calendar.html.twig', [
            'events' => $eventsArray
        ]);
    }
}

    // #[Route('/admin/organisation/update/{id}', name: 'admin_organisation_update', methods: ['GET', 'POST'])]
    // public function admin_update(Request $request, int $id): Response
    // {
    //     /** @var Organisation $organisation */
    //     $admin = $this->getUser();

    //     if (!$admin){
    //         throw $this->createNotFoundException('Admin introuvable!');
    //     }

    //     $organisation = $this->em->getRepository(Organisation::class)->find($id);

    //     $form = $this->createForm(RegisterType::class, $organisation)->handleRequest($request);

    //     if($form->isSubmitted() && $form->isValid()){
    //         $plainPassword = $form->get('plainPassword')->getData();
    //         if(!empty($plainPassword)){
    //             $organisation->setPassword($this->userPasswordHasher->hashPassword($organisation, $plainPassword));
    //         }
    //         $this->em->persist($organisation);
    //         $this->em->flush();

    //         return $this->redirectToRoute('home');
    //     }

    //     return $this->render('home/register.html.twig', [
    //         'form' => $form->createView(),
    //         'buttonName' => 'Modifier',
    //     ]);
    // }


    // #[Route('/admin/organisation/delete/{id}', name: 'admin_organisation_delete', methods: ['GET', 'POST'])]
    // public function admin_delete(int $id): Response
    // {
    //     /** @var Organisation|null $admin */
    //     $admin = $this->getUser();

    //     if (!$admin){
    //         throw $this->createNotFoundException('Admin introuvable!');
    //     }

    //     $organisation = $this->em->getRepository(Organisation::class)->find($id);
    //     $this->em->remove($organisation);
    //     $this->em->flush();

    //     return $this->redirectToRoute('admin_organisations');
    // }


