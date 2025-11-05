<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Organisation;
use App\Entity\Location;
use App\Entity\User;
use App\Enum\FeeEnum;
use App\Enum\PublicEnum;
use App\Enum\ThematicEnum;
use DateTime;
use Faker\Factory;


class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;
   
    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher,
        )
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        

        // $fee = ["Gratuit", "Prix libre", "Payant"];
        // $public = ["Enfants", "Ados", "Adultes", "Tout public"];
        // $thematic = ["Sport", "Culture", "Politique", "Gastronomie", "Art", "Sciences", "Environnement"];
        
        $feeCases = FeeEnum::cases();
        $publicCases = PublicEnum::cases();
        $thematicCases = ThematicEnum::cases();
        $town = ["Barcelonnette", "Jausiers", "Saint-Pons", "Ubaye Serre-Ponçon", "Uvernet-Fours", "Enchastrayes", "Saint-Paul usr Ubaye", "Val d'Oronaye", "Faucon de Barcelonnette", "Le Lauzet sur Ubaye", "Meolans Revel", "Les Thuiles", "La Condamine Chatelard"];
        $status = ["Association", "Institution", "Commerçant"];
        $locationNames = ["Mairie", "Salle des fêtes", "El Zocalo", "Séolane", "Le Grain de Sable", "Lou Fresc"];

        // Create Locations
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
        $users = [];

        for ($k = 1; $k < 5; $k++) {
            $user = new User();
            $user->setEmail("user". $k . "@email.com");
            $user->setPhone($faker->phoneNumber());
            $user->setFirstName("userfirstname" . $k);
            $user->setLastName("userlastname" . $k);
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        $usertest = new User();
        $usertest->setEmail("usertest@email.com");
        $usertest->setPhone($faker->phoneNumber());
        $usertest->setFirstName("usertestfirstname");
        $usertest->setLastName("usertestlastname");
        $usertest->setRoles(["ROLE_USER"]);
        $usertest->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $manager->persist($usertest);
        $users[] = $usertest;

        $admin = new User();
        $admin->setEmail("admin@email.com");
        $admin->setPhone($faker->phoneNumber());
        $admin->setFirstName("adminfirstname");
        $admin->setLastName("adminlastname");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $manager->persist($admin);

        // Create Organisations
        $organisations = [];

        $organisation = new Organisation();
        $organisation->setName("OrgaTest");
        $organisation->addContact($usertest);
        $organisation->addContact($admin);
        $organisation->setEmail("orgatest@emal.com");
        $organisation->setAddress("1, rue Sésame");
        $organisation->setTown("Barcelonnette");
        $organisation->setStatus("Asso");
        $manager->persist($organisation);
        $organisations[] = $organisation;

        for ($i = 1; $i < 5; $i++) {
            $orga = new Organisation();
            $orga->setName("Organisation " . $i);
            $orga->setEmail("orga" . $i . "@email.com");
            $orga->setTown($town[array_rand($town)]);
            $orga->setStatus($status[array_rand($status)]);
            for ($c = 1; $c < random_int(1,4); $c++){
                $orga->addContact($users[random_int(0,count($users)-1)]);
            }
            $manager->persist($orga);
            $organisations[] = $orga;
        }

        // Create Events
        for ($j=1; $j < 20; $j++) {
            $event = new Event();
            $event->setOrganisation($organisations[random_int(0,count($organisations)-1)]);
            $event->setName("EventName" . $j);
            $event->setStartDate(new \DateTime());
            $event->setLocation($locations[random_int(0,count($locations)-1)]);
            $event->setDescription($faker->paragraphs(10, true));
            $event->setPoster($faker->imageUrl(330, 500, 'poster', true));
            $event->setComment($faker->paragraphs(10, true));
            
            $randomFee = $feeCases[random_int(0, count($feeCases)-1)];
            $event->setFee($randomFee);

            $randomThematic = $thematicCases[random_int(0, count($thematicCases)-1)];
            $event->setThematic($randomThematic);

            $randomPublic = $publicCases[random_int(0, count($publicCases)-1)];
            $event->setPublic($randomPublic);
            
            $manager->persist($event);
        }

        $manager->flush();
    }
}
