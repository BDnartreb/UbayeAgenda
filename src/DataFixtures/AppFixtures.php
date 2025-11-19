<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Organisation;
use App\Entity\Location;
use App\Enum\FeeEnum;
use App\Enum\PublicEnum;
use App\Enum\StatusEnum;
use App\Enum\ThematicEnum;
use App\Enum\TownEnum;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private $params;
   
    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher,
        ParameterBagInterface $params,
        )
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->params = $params;
    }

    
    /**
     * This method must return an array of groups
     * on which the implementing class belongs to
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
            return ['dev'];
    }
    
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        $feeCases = FeeEnum::cases();
        $publicCases = PublicEnum::cases();
        $thematicCases = ThematicEnum::cases();
        $statusCases = StatusEnum::cases();
        $townCases = TownEnum::cases();
        $locationNames = ["Mairie", "Salle des fêtes", "El Zocalo", "Séolane", "Le Grain de Sable", "Lou Fresc"];

        // Create Locations
        $locations = [];

        foreach ($locationNames as $locationName){
            $location = new Location;
            $location->setName($locationName);
            $location->setAddress("Adresse du lieu de l'événement");

            $randomTown = $townCases[random_int(0,count($townCases)-1)];
            $location->setTown($randomTown);

            $manager->persist($location);
            $locations[] = $location;
        }

        // Create Admin
   
        $admin = new Organisation();
        $admin->setName("AdminDev");
        $admin->setAddress("");
        $admin->setTown(TownEnum::A);
        //$admin->setEmail($_ENV['ADMIN_EMAIL']);
        $admin->setEmail($this->params->get('admin_email'));
        $admin->setPhone("");
        $admin->setFirstName("");
        $admin->setLastName("");
        $admin->setRoles(["ROLE_ADMIN"]);
        //$admin->setPassword($this->userPasswordHasher->hashPassword($admin, $_ENV['ADMIN_PASSWORD']));
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, $this->params->get('admin_password')));
        $admin->setStatus(StatusEnum::INDIVIDUAL);
        
        $manager->persist($admin); 

        $test = new Organisation();
        $test->setName("OrgaTest");
        $test->setAddress("");
        $test->setTown(TownEnum::A);
        $test->setEmail('orgatest@email.com');
        $test->setPhone("");
        $test->setFirstName("OrgaTest");
        $test->setLastName("ORGATEST");
        $test->setRoles(["ROLE_ORGANISATION"]);
        $test->setPassword($this->userPasswordHasher->hashPassword($test, 'password'));
        $test->setStatus(StatusEnum::INDIVIDUAL);

        $manager->persist($test);    

        // Create Organisations
        $organisations = [];

        for ($i = 1; $i < 5; $i++) {
            $orga = new Organisation();
            $orga->setName("Organisation " . $i);
            $orga->setAddress("$i, rue Sésame");

            $randomTown = $townCases[random_int(0,count($townCases)-1)];
            $orga->setTown($randomTown);

            $orga->setEmail("orga" . $i . "@email.com");
            $orga->setPhone($faker->phoneNumber());
            $orga->setFirstName("orga" . $i);
            $orga->setLastName("ORGA" . $i);
            $orga->setRoles(["ROLE_ORGANISATION"]);
            $orga->setPassword($this->userPasswordHasher->hashPassword($orga, 'password'));

            $randomStatus = $statusCases[random_int(0,count($statusCases)-1)];
            $orga->setStatus($randomStatus);
            
            $manager->persist($orga);
            $organisations[] = $orga;
        }

        // Create Events
        for ($j=11; $j < 27; $j++) {
            $event = new Event();
            $event->setOrganisation($organisations[random_int(0,count($organisations)-1)]);
            $event->setName("EventName" . $j);
            //$event->setStartDate(new \DateTime());
            $startDate = $faker->dateTimeBetween('-2 days', '+10 days');
            $event->setStartDate($startDate);

            if($faker->boolean(50)) {
                $endDate = (clone $startDate)->modify('+' . $faker->numberBetween(1, 5) . ' hours');
                $event->setEndDate($endDate);
            } else{
                $event->setEndDate(null);
            }
            $event->setLocation($locations[random_int(0,count($locations)-1)]);    
            $event->setDescription($faker->paragraphs(10, true));
            $event->setPoster(sprintf('uploads/' . $j . '.png'));
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
