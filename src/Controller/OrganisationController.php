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

    #[Route('/organisation/update', name: 'organisation_update', methods: ['GET', 'POST'])]
    public function update(Request $request): Response
    {
        /** @var Organisation|null $organisation */
        $organisation = $this->getUser();

        if (!$organisation){
            throw $this->createNotFoundException('Organisation introuvable!');
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

    #[Route('/organisation/delete', name: 'organisation_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        TokenStorageInterface $tokenStorage
        ): Response
    {
        /** @var Organisation|null $organisation */
        $organisation = $this->getUser();

        if (!$organisation){
            throw $this->createNotFoundException('Organisation introuvable!');
        }

        // Check CSRF token
        if (!$this->isCsrfTokenValid('delete'.$organisation->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('home');
        }

        $currentUser = $tokenStorage->getToken()?->getUser();
        $isCurrentUser = ($currentUser === $organisation);

        // Logout user if connected
        if ($isCurrentUser) {
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();
        }

        $this->em->remove($organisation);
        $this->em->flush();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        
        return $this->redirectToRoute('home');
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


    #[Route('/admin/organisation/delete/{id}', name: 'admin_organisation_delete', methods: ['GET', 'POST'])]
    public function admin_delete(int $id): Response
    {
        /** @var Organisation|null $admin */
        $admin = $this->getUser();

        if (!$admin){
            throw $this->createNotFoundException('Admin introuvable!');
        }

        $organisation = $this->em->getRepository(Organisation::class)->find($id);
        $this->em->remove($organisation);
        $this->em->flush();

        return $this->redirectToRoute('admin_organisations');
    }

    /**
     * show organisation page
     */
    #[Route('/organisation', name: 'organisation')]
    public function organisation(): Response
    {
        /** @var \App\Entity\Organisation|null $organisation */
        $organisation = $this->getUser();
        $organisationId = $organisation->getId();
        $events = $this->em->getRepository(Event::class)->findEventsByOrganisationOrderedByStartDateFromToday($organisationId);

        return $this->render('organisation/organisation.html.twig', [
            'organisation' => $organisation,
            'events' => $events,
        ]);
    }

        /**
     * show list of organisations
     */
    #[Route('/admin/organisations', name: 'admin_organisations')]
    public function organisations(): Response
    {
        /** @var Organisation|null $connectedUser */
        $connectedUser = $this->getUser();
        $admin = $this->em->getRepository(Organisation::class)->findOneBy(['email' => 'admin@ubayeagenda.com']);

        if ($connectedUser === $admin){
            $organisations = $this->em->getRepository(Organisation::class)->findByRole('ROLE_ORGANISATION');

            return $this->render('admin/organisations.html.twig', [
                'organisations' => $organisations,
            ]);
        }

        return $this->redirectToRoute('home');
    }
}


// #[Route('/organisation/delete/{id}', name: 'admin_orga_delete', methods: ['POST'])]
// public function delete(Request $request, Organisation $organisation, TokenStorageInterface $tokenStorage): Response
// {
//     // Vérifie le token CSRF
//     if (!$this->isCsrfTokenValid('delete'.$organisation->getId(), $request->request->get('_token'))) {
//         $this->addFlash('error', 'Token CSRF invalide.');
//         return $this->redirectToRoute('admin_orga_list');
//     }

//     // Sécurité : autorisation via le voter
//     $this->denyAccessUnlessGranted('EDIT', $organisation);

//     $currentUser = $tokenStorage->getToken()?->getUser();
//     $isCurrentUser = ($currentUser === $organisation);

//     // Supprime l'organisation
//     $this->em->remove($organisation);
//     $this->em->flush();

//     // Déconnexion si on supprime l'utilisateur connecté
//     if ($isCurrentUser) {
//         $tokenStorage->setToken(null);
//         $request->getSession()->invalidate();
//         $this->addFlash('success', 'Votre compte a été supprimé.');
//         return $this->redirectToRoute('app_login');
//     }

//     $this->addFlash('success', 'Organisation supprimée avec succès.');
//     return $this->redirectToRoute('admin_orga_list');
// }
