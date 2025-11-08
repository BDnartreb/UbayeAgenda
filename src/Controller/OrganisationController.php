<?php

namespace App\Controller;

use App\Entity\Organisation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\RegisterType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
            $organisation->setPassword($this->userPasswordHasher->hashPassword($organisation, $plainPassword));
            $organisation->setRoles(['ROLE_USER']);
            $this->em->persist($organisation);
            $this->em->flush();
            $this->addFlash('success', 'Inscription réussie. Vous pouvez vous connecter !');
            
            return $this->redirectToRoute('login');
        }

        return $this->render('home/register.html.twig', ['form' => $form]);
    }

    #[Route('/organisation/update/{id}', name: 'organisation_update')]
    public function update(Request $request, int $id): Response
    {
        $organisation = $this->em->getRepository(Organisation::class)->find($id);
        $form = $this->createForm(RegisterType::class, $organisation)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $plainPassword = $form->get('plainPassword')->getData();
            if(!empty($plainPassword)){
                $organisation->setPassword($this->userPasswordHasher->hashPassword($organisation, $plainPassword));
            }
            $this->em->persist($organisation);
            $this->em->flush();

            return $this->redirectToRoute('login');
        }

        return $this->render('home/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/organisation/delete/{id}', name: 'admin_orga_delete')]
    public function delete(): Response
    {
        return $this->render('organisation/delete.html.twig', [
            'controller_name' => 'OrganisationController',
        ]);
    }

    /**
     * show list of organisations
     */
    #[Route('/admin/organisations', name: 'organisations')]
    public function organisations(): Response
    {
        return $this->render('organisation/index.html.twig', [
            'controller_name' => 'OrganisationController',
        ]);
    }

    /**
     * show organisation page
     */
    #[Route('/organisation/{id}', name: 'organisation')]
    public function organisation(int $id): Response
    {
        return $this->render('organisation/organisation.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    // #[Route('/organisation/add', name: 'orga_add')]
    // public function add(Request $request, EntityManagerInterface $em): Response
    // {
    //     $organisation = new Organisation();

    //     $form = $this->createForm(OrganisationType::class, $organisation)->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {

    //         $em->persist($organisation);
    //         $em->flush();
    //         $this->addFlash('success', 'Votre organisation a bien été enregistrée !');

    //         // Back to the previous page
    //         $referer = $request->headers->get('referer');
    //         if ($referer) {
    //             return $this->redirect($referer);
    //         }

    //         // Stay on the current page
    //         return $this->redirectToRoute(
    //             $request->attributes->get('_route'),
    //             $request->attributes->get('_route_params')
    //         );
    //     }

    //     return $this->render('/user/organisationForm.html.twig', ['form' => $form]);
    // }



    


}
