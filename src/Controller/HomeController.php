<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EventRepository $eventRepository,): Response
    {    
        return $this->redirectToRoute('eventlist');
    }

    #[Route('/eventlist', name: 'eventlist')]
    public function eventlist(EventRepository $eventRepository,): Response
    {
        $events = $eventRepository->findAllOrderedByStartDateFromNow();
        return $this->render('home/eventlist.html.twig', [
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
