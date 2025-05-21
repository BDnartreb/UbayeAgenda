<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Organisation;
use App\Entity\User;
use DateTime;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create Users
        $user = new User();
        $user->setEmail("user@email.com");
        $user->setFirstName("userfirstname");
        $user->setLastName("userlastname");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));

        $admin = new User();
        $admin->setEmail("admin@email.com");
        $admin->setFirstName("adminfirstname");
        $admin->setLastName("adminlastname");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $manager->persist($admin);

        // Create Organisations
        $organisation = new Organisation();
        $organisation->setName("Orga1");
        $organisation->addContact($user);
        $organisation->addContact($admin);
        $organisation->setEmail("orga1@emal.com");
        $organisation->setTown("Barcelonnette");
        $organisation->setStatus("Asso");
        $manager->persist($organisation);

        $orga2 = new Organisation();
        $orga2->setName("Orga2");
        $orga2->setEmail("orga2@emal.com");
        $orga2->setTown("Jausiers");
        $orga2->setStatus("Asso");
        $manager->persist($orga2);

        //$user->addOrganisation($organisation);

        $manager->persist($user);


        // Create Events
        $event = new Event();
        $event->setOrganisation($organisation);
        $event->setName("EventName");
        $event->setStartDate(new DateTime());
        $event->setPlace("C'est ici!");
        $event->setTown("Jausiers");
        $event->setFee('Gratuit');
        $event->setThematic("Art");
        $event->setPublic("Familial");

        $manager->persist($event);

        $manager->flush();
    }
}
