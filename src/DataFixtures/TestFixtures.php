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

class TestFixtures extends Fixture implements FixtureGroupInterface
{
    private $userPasswordHasher;
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
            return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        
        $feeCases = FeeEnum::cases();
        $publicCases = PublicEnum::cases();
        $thematicCases = ThematicEnum::cases();
        $statusCases = StatusEnum::cases();
        $townCases = TownEnum::cases();
        $locationNames = ["LocationTest", "Mairie", "Salle des fêtes", "El Zocalo", "Séolane", "Le Grain de Sable", "Lou Fresc"];

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
        $admin->setName("AdminTest");
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
        $admin->setStatus(StatusEnum::PROFESSIONAL);
        
        $manager->persist($admin); 

        $orgaTest = new Organisation();
        $orgaTest->setName("OrgaTest");
        $orgaTest->setAddress("");
        $orgaTest->setTown(TownEnum::A);
        $orgaTest->setEmail('orgatest@email.com');
        $orgaTest->setPhone("");
        $orgaTest->setFirstName("OrgaTest");
        $orgaTest->setLastName("ORGATEST");
        $orgaTest->setRoles(["ROLE_ORGANISATION"]);
        $orgaTest->setPassword($this->userPasswordHasher->hashPassword($orgaTest, 'password'));
        $orgaTest->setStatus(StatusEnum::PROFESSIONAL);

        $manager->persist($orgaTest);    

        $orgaTest2 = new Organisation();
        $orgaTest2->setName("OrgaTest2");
        $orgaTest2->setAddress("");
        $orgaTest2->setTown(TownEnum::B);
        $orgaTest2->setEmail('orgatest2@email.com');
        $orgaTest2->setPhone("");
        $orgaTest2->setFirstName("OrgaTest2");
        $orgaTest2->setLastName("ORGATEST2");
        $orgaTest2->setRoles(["ROLE_ORGANISATION"]);
        $orgaTest2->setPassword($this->userPasswordHasher->hashPassword($orgaTest2, 'password'));
        $orgaTest2->setStatus(StatusEnum::PROFESSIONAL);

        $manager->persist($orgaTest2);    

        $orgaToDelete = new Organisation();
        $orgaToDelete->setName("OrgaToDelete");
        $orgaToDelete->setAddress("");
        $orgaToDelete->setTown(TownEnum::A);
        $orgaToDelete->setEmail('orgatodelete@email.com');
        $orgaToDelete->setPhone("");
        $orgaToDelete->setFirstName("OrgaToDelete");
        $orgaToDelete->setLastName("ORGATODELETE");
        $orgaToDelete->setRoles(["ROLE_ORGANISATION"]);
        $orgaToDelete->setPassword($this->userPasswordHasher->hashPassword($orgaToDelete, 'password'));
        $orgaToDelete->setStatus($statusCases[random_int(0,count($statusCases)-1)]);

        $manager->persist($orgaToDelete);  


        // Create Events for orgaTest
        for ($i=1; $i < 5; $i++) {
            $event = new Event();
            $event->setOrganisation($orgaTest);
            $event->setName("EventTestName" . $i);
                $startDate = $faker->dateTimeBetween('-2 days', '+10 days');
            $event->setStartDate($startDate);
                $endDate = (clone $startDate)->modify('+' . $faker->numberBetween(1, 5) . ' hours');
            $event->setEndDate($endDate);
            $event->setLocation($locations[random_int(0,count($locations)-1)]);    
            $event->setDescription($faker->paragraphs(10, true));
            $event->setPoster($faker->imageUrl(330, 500, 'poster', true));
            $event->setComment($faker->paragraphs(10, true));
            $event->setFee($feeCases[random_int(0, count($feeCases)-1)]);
            $event->setThematic($thematicCases[random_int(0, count($thematicCases)-1)]);
            $event->setPublic($publicCases[random_int(0, count($publicCases)-1)]);
            
            $manager->persist($event);
        }

        // Create Events for orgaToDelete
        for ($j=1; $j < 5; $j++) { 
            $event = new Event();
            $event->setOrganisation($orgaToDelete);
            $event->setName("EventToDeleteName" . $j);
                $startDate = $faker->dateTimeBetween('-2 days', '+10 days');
            $event->setStartDate($startDate);
                $endDate = (clone $startDate)->modify('+' . $faker->numberBetween(1, 5) . ' hours');
            $event->setEndDate($endDate);
            $event->setLocation($locations[random_int(0,count($locations)-1)]);    
            $event->setDescription($faker->paragraphs(10, true));
            $event->setPoster($faker->imageUrl(330, 500, 'poster', true));
            $event->setComment($faker->paragraphs(10, true));
            $event->setFee($feeCases[random_int(0, count($feeCases)-1)]);
            $event->setThematic($thematicCases[random_int(0, count($thematicCases)-1)]);
            $event->setPublic($publicCases[random_int(0, count($publicCases)-1)]);
            
            $manager->persist($event);
        }

        $manager->flush();
    }


}
