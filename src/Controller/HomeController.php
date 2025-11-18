<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Organisation;
use App\Enum\FeeEnum;
use App\Enum\PublicEnum;
use App\Enum\ThematicEnum;
use App\Enum\TownEnum;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {    
        return $this->redirectToRoute('eventlist');
    }

    // #[Route('/eventlist', name: 'eventlist')]
    // public function eventlist(EventRepository $eventRepository): Response
    // {
    //     $events = $eventRepository->findAllOrderedByStartDateFromToday();
    //     return $this->render('home/eventlist.html.twig', [
    //         'controller_name' => 'HomeController',
    //         'events' => $events,
    //     ]);
    // }

    #[Route('/eventlist', name: 'eventlist', methods: ['GET'])]
    public function eventlist(EventRepository $eventRepository, Request $request): Response
    {
        //Filter Parameters
        $organisations = $this->em->getrepository(Organisation::class)->findAll();
        $thematics = ThematicEnum::cases();
        $fees = FeeEnum::cases();
        $publics = PublicEnum::cases();
        $towns = TownEnum::cases();
        $locations = $this->em->getrepository(Location::class)->findAll();

        // Filters selected
        $selectedThematics = $request->query->all('thematics');
        $selectedFees = $request->query->all('fees');
        $selectedPublics = $request->query->all('publics');
        $selectedTowns = $request->query->all('towns'); 

        $selectedOrganisations = $request->query->all('organisations');
            $selectedOrganisationsId = [];
            foreach ($selectedOrganisations as $orgaName) {
                $organisationId = $this->em->getRepository(Organisation::class)->findOneBy(['name' => $orgaName])->getId();
                $selectedOrganisationsId[] = $organisationId;
            }

        $selectedLocations = $request->query->all('locations');
            $selectedLocationsId = [];
            foreach ($selectedLocations as $locationName) {
                $locationId = $this->em->getRepository(Location::class)->findOneBy(['name' => $locationName])->getId();
                $selectedLocationsId[] = $locationId;
            }


        //events selected
        $events = $eventRepository->findEventsByFiltersOrderedByStartDateFromToday(
            fees: $selectedFees,
            thematics: $selectedThematics,
            publics: $selectedPublics,
            towns: $selectedTowns,
            organisations: $selectedOrganisationsId,
            locations: $selectedLocationsId,
        );

        $noEventSelectedMessage = null;
        if (!$events){
            $noEventSelectedMessage = "Aucun événement ne correspond à la sélection";
        }

        // filter parameters and filters selected
        return $this->render('home/eventlist.html.twig', [
            'controller_name' => 'HomeController',
            'events' => $events,
            'thematics' => $thematics,
            'fees' => $fees,
            'publics' => $publics,
            'towns' => $towns,
            'organisations' => $organisations,
            'locations' => $locations,
            'selectedThematics' => $selectedThematics,   
            'selectedFees' => $selectedFees,            
            'selectedPublics' => $selectedPublics,            
            'selectedTowns' => $selectedTowns,            
            'selectedOrganisations' => $selectedOrganisations,            
            'selectedLocations' => $selectedLocations,
            'noEventSelectedMessage' => $noEventSelectedMessage,
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
