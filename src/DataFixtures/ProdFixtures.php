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


class ProdFixtures extends Fixture implements FixtureGroupInterface
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
        
        // Create Admin
   
        $admin = new Organisation();
        $admin->setName("Admin");
        $admin->setAddress("");
        $admin->setTown(TownEnum::A);
        $admin->setEmail($_ENV['ADMIN_PASSWORD']);
        $admin->setPhone("");
        $admin->setFirstName("");
        $admin->setLastName("");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, $_ENV['ADMIN_PASSWORD']));
        $admin->setStatus(StatusEnum::INDIVIDUAL);

        $manager->persist($admin);    

        $manager->flush();
    }

    /**
     * This method must return an array of groups
     * on which the implementing class belongs to
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
            return ['prod'];
    }
}
