<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Organisation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\RegisterType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Form\FormError;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
            $organisation->setRoles(['ROLE_USER']);

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
        /** @var Organisation $organisation */
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

            return $this->redirectToRoute('home');
        }

        return $this->render('home/register.html.twig', [
            'form' => $form->createView(),
            'buttonName' => 'Modifier',
        ]);
    }

    #[Route('/organisation/delete', name: 'orga_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        TokenStorageInterface $tokenStorage
        ): Response
    {
        /** @var Organisation $organisation */
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
        
        return $this->redirectToRoute('home'); // page publique après suppression
    }

    /**
     * show organisation page
     */
    #[Route('/organisation', name: 'organisation')]
    public function organisation(): Response
    {
        /** @var \App\Entity\Organisation $organisation */
        $organisation = $this->getUser();
        $events = $this->em->getRepository(Event::class)->findBy(['organisation' => $organisation->getId()]);

        //dd($organisation);
        //$organisation = $this->em->getRepository(Organisation::class)->find($id);

        return $this->render('organisation/organisation.html.twig', [
            'controller_name' => 'HomeController',
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
        $organisations = $this->em->getRepository(Organisation::class)->findByRole('ROLE_ORGANISATION');

        return $this->render('admin/organisations.html.twig', [
            'controller_name' => 'OrganisationController',
             'organisations' => $organisations,
        ]);
    }

       /**
     * show list of organisations
     */
    #[Route('/admin/events', name: 'admin_events')]
    public function events(): Response
    {
        $events = $this->em->getRepository(Event::class)->findAll();
        
        return $this->render('/admin/events.html.twig', [
            'controller_name' => 'OrganisationController',
            'events' => $events,

        ]);
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
