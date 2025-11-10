<?php

namespace App\Tests\Controller;

use App\Entity\Organisation;
use App\Enum\TownEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrganisationControllerTest extends AbstractControllerTest
{
    public function testRegisterIsSuccessfull():void
    {
        $crawler = $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        //dump($this->client->getResponse()->getContent());

        $newOrgaName = "NewOrga";
        $newOrgaEmail = "neworga@email.com";

        $form = $crawler->filter('form[name="register"]')->form([ 
            'register[name]' => $newOrgaName,
            'register[address]' => "NewOrgaAdress",
            'register[town]' => '2',
            'register[email]' => $newOrgaEmail,
            'register[phone]' => "0123456789",
            'register[status]' => "0",
            'register[firstName]' => "NewOrga",
            'register[lastName]' => "NEWORGA",
            'register[plainPassword]' => "password",
            'charter' => 'on',
        ]);

            $this->client->submit($form);

            $newOrga = $this->getOrganisation($newOrgaEmail);
            $this->assertNotNull($newOrga, 'L’organisation doit avoir été créée');
            $this->assertEquals($newOrgaName, $newOrga->getName());
            $this->assertNotSame('password', $newOrga->getPassword());
            
            //$this->assertTrue($this->client->getResponse()->isRedirect());
            $this->assertResponseRedirects('/logout');
            $this->client->followRedirect();
            $this->assertResponseRedirects('/login');
            $this->client->followRedirect();

            $this->assertSelectorExists('form');
            $this->assertSelectorTextContains('h1', 'Connexion');

            // Called from AbstractControllerTest
            $this->scheduleForRemoval($newOrga);
    }

    // public function testBadRegisterRenderRegisterPage():void
    // {
    //     $crawler = $this->client->request('GET', '/register');
    //     $this->assertResponseIsSuccessful();
    //     $this->assertSelectorExists('form');
    //     //dump($this->client->getResponse()->getContent());

    //     $newOrgaName = "NewOrga";
    //     $newOrgaEmail = "neworga@email.com";

    //     $form = $crawler->filter('form[name="register"]')->form([ 
    //         'register[name]' => $newOrgaName,
    //         'register[address]' => "NewOrgaAdress",
    //         'register[town]' => '',
    //         'register[email]' => $newOrgaEmail,
    //         'register[phone]' => "0123456789",
    //         'register[status]' => "0",
    //         'register[firstName]' => "NewOrga",
    //         'register[lastName]' => "NEWORGA",
    //         'register[plainPassword]' => "password",
    //         'charter' => 'on',
    //     ]);

    //         $this->client->submit($form);

    //         $newOrga = $this->getOrganisation($newOrgaEmail);
    //         $this->assertNotNull($newOrga, 'L’organisation doit avoir été créée');
    //         $this->assertEquals($newOrgaName, $newOrga->getName());
    //         $this->assertNotSame('password', $newOrga->getPassword());
            
    //         //$this->assertTrue($this->client->getResponse()->isRedirect());
    //         $this->assertResponseRedirects('/logout');
    //         $this->client->followRedirect();
    //         $this->assertResponseRedirects('/login');
    //         $this->client->followRedirect();

    //         $this->assertSelectorExists('form');
    //         $this->assertSelectorTextContains('h1', 'Connexion');

            

    // }

    // private EntityManagerInterface $em;
    // private KernelBrowser $client;

    // public function setUp(): void
    // {
    //     $this->client = static::createClient();
    //     $container = $this->client->getContainer();
    //     $this->em = $container->get('doctrine')->getManager();
    //     $orgatest = $this->em->getRepository(Organisation::class)->findOneBy(['email' => 'test@email.com']);
    //     $this->client->loginUser($orgatest);
    // }

    // public function testIndex(): void
    // {
    //     $client = static::createClient();
    //     $client->request('GET', '/organisation');

    //     self::assertResponseIsSuccessful();
    // }
}
