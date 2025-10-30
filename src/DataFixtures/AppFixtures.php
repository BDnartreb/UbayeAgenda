<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Organisation;
use App\Entity\Location;
use App\Entity\User;
use DateTime;
//use Faker\Generator;
//use Symfony\Bundle\MakerBundle\Generator;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;
    //private readonly Generator $faker;
    
    //public function __construct(UserPasswordHasherInterface $userPasswordHasher, Generator $faker)
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
       // $this->$faker = $faker;
    }

    public function load(ObjectManager $manager): void
    {
        $fee = ["Gratuit", "Prix libre", "Payant"];
        $public = ["Enfants", "Ados", "Adultes", "Tout public"];
        $thematic = ["Sport", "Culture", "Politique", "Gastronomie", "Art", "Sciences", "Environnement"];
        $town = ["Barcelonnette", "Jausiers", "Saint-Pons", "Ubaye Serre-Ponçon", "Uvernet-Fours", "Enchastrayes", "Saint-Paul usr Ubaye", "Val d'Oronaye", "Faucon de Barcelonnette", "Le Lauzet sur Ubaye", "Meolans Revel", "Les Thuiles", "La Condamine Chatelard"];
        $status = ["Association", "Institution", "Commerçant"];
        $locationNames = ["Mairie", "Salle des fêtes", "El Zocalo", "Séolane", "Le Grain de Sable", "Lou Fresc"];

        $locations = [];

        foreach ($locationNames as $locationName){
            $location = new Location;
            $location->setName($locationName);
            $location->setAddress("Adresse du lieu de l'événement");
            $location->setTown($town[array_rand($town)]);
            $manager->persist($location);
            $locations[] = $location;
        }

        // Create Users
        $user = new User();
        $user->setEmail("user@email.com");
        $user->setFirstName("userfirstname");
        $user->setLastName("userlastname");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        $admin = new User();
        $admin->setEmail("admin@email.com");
        $admin->setFirstName("adminfirstname");
        $admin->setLastName("adminlastname");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $manager->persist($admin);

        // Create Organisations

        for ($i = 1; $i < 5; $i++) {
            $orga = new Organisation();
            $orga->setName("Organisation " . $i);
            $orga->setEmail("orga" . $i . "@email.com");
            $orga->setTown($town[array_rand($town)]);
            $orga->setStatus($status[array_rand($status)]);
            $manager->persist($orga);
        }

        $organisation = new Organisation();
        $organisation->setName("OrgaTest");
        $organisation->addContact($user);
        $organisation->addContact($admin);
        $organisation->setEmail("orgatest@emal.com");
        $organisation->setAddress("1, rue Sésame");
        $organisation->setTown("Barcelonnette");
        $organisation->setStatus("Asso");
        $manager->persist($organisation);

        //$user->addOrganisation($organisation);

       // $manager->persist($user);


        // Create Events
        $event = new Event();
        $event->setOrganisation($organisation);
        $event->setName("EventName");
        $event->setStartDate(new DateTime());
        $event->setLocation($locations[0]);
        $event->setFee('Gratuit');
        $event->setThematic("Art");
        $event->setPublic("");

        $manager->persist($event);


       
        for ($j=1; $j < 20; $j++) {
            $event = new Event();
            $event->setOrganisation($organisation);
            $event->setName("EventName" . $j);
            $event->setStartDate(new DateTime());
            $event->setLocation($locations[random_int(0,5)]);
            //$event->setDescription($this->faker->paragraphs(10, true));
            //$event->setComment($this->faker->paragraphs(10, true));
            $event->setFee($fee[array_rand($fee)]);
            $event->setThematic($thematic[array_rand($thematic)]);
            $event->setPublic($public[array_rand($public)]);
        }

        $manager->flush();
    }
}
