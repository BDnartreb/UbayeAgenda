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
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserController extends AbstractController
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
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);
            $organisations = $form->get('organisations')->getData();
            foreach ($organisations as $organisation) {
                $organisation->addContact($user);
                $this->em->persist($organisation);
            }
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash('success', 'Inscription réussie. Vous pouvez vous connecter !');
            
            return $this->redirectToRoute('login');
        }

        return $this->render('home/register.html.twig', ['form' => $form]);
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

    // REVOIR update et delete par User et Admin
    #[Route('/user/update/{id}', name: 'user_update')]
    public function update(Request $request, int $id): Response
    {
        $user = $this->em->getRepository(User::class)->find($id);
        $form = $this->createForm(RegisterType::class, $user)->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $plainPassword = $form->get('plainPassword')->getData();
            if(!empty($plainPassword)){
                $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));
            }
            $this->em->persist($user);
            $this->em->flush();

            return $this->redirectToRoute('login');
        }

        return $this->render('home/register.html.twig', [
            'form' => $form->createView()
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
