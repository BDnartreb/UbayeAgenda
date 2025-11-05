<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EventRepository $eventRepository,): Response
    {
        $events = $eventRepository->findAll();
        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'events' => $events,
        ]);
    }

    #[Route('/posters', name: 'posters')]
    public function posters(): Response
    {
        return $this->render('home/posters.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/charter', name: 'charter')]
    public function about(): Response
    {
        return $this->render('home/charter.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

}
