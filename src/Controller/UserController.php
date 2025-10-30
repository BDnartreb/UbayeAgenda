<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/register', name: 'register', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function register(Request $request, EntityManagerInterface $entityManager,
     UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);
            $organisations = $form->get('organisations')->getData();
            foreach ($organisations as $organisation) {
                $organisation->addContact($user);
                $entityManager->persist($organisation);
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Inscription réussie. Vous pouvez vous connecter !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('home/register.html.twig', ['form' => $form]);
    }

    #[Route('/user/update/{id}', name: 'user_update')]
    public function update(): Response
    {
        return $this->render('home/register.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/profile', name: 'user_profile')]
    public function userProfile(): Response
    // #[Route('/user/profile/{id}', name: 'user_profile')]
    // public function userProfile(int $id): Response
    {
        return $this->render('user/profile.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/admin/user/delete/{id}', name: 'user_delete')]
    public function delete(): Response
    {
        return $this->render('user/delete.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
      
    #[Route('/admin/users', name: 'admin_users')]
    public function adminUsers(): Response
    {
        return $this->render('admin/users.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

}
