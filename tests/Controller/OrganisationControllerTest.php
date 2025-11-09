<?php

namespace App\Tests\Controller;

use App\Entity\Organisation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrganisationControllerTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $orgatest = $this->em->getRepository(Organisation::class)->findOneBy(['email' => 'test@email.com']);
        $this->client->loginUser($orgatest);
    }

    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/organisation');

        self::assertResponseIsSuccessful();
    }
}
