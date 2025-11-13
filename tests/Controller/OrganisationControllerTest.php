<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\Organisation;
use Symfony\Component\HttpFoundation\Response;

final class OrganisationControllerTest extends AbstractControllerTest
{
    public function testRegisterIsSuccessful():void
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
// FAILED Cf AbstractControllerTest
            // $this->scheduleForRemoval($newOrga);
    }

    /**
     * Test of NotBlank and Uniq_Email
     * @dataProvider provideInvalidRegisterData
     */
    public function testRegisterWithInvalidDataFailed(array $formData, string $expectedErrorMessage):void
    {
        $crawler = $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="register"]');

        // Merge les données valides avec celles invalides
        $form = $crawler->filter('form[name="register"]')->form(array_merge([
            'register[name]' => "OrgaTest",
            'register[address]' => "OrgaAdress",
            'register[town]' => '2',
            'register[email]' => 'orga@email.com',
            'register[phone]' => "0123456789",
            'register[status]' => "0",
            'register[firstName]' => "OrgaTest",
            'register[lastName]' => "ORGATEST",
            'register[plainPassword]' => "password",
            'charter' => 'on',
        ], $formData));

        $this->client->submit($form);
        // Symfony 5/6 renvoie soit 200 (OK) soit 422 (Unprocessable Entity) pour les formulaires invalides
        $this->assertContains(
            $this->client->getResponse()->getStatusCode(),
            [Response::HTTP_OK, Response::HTTP_UNPROCESSABLE_ENTITY],
            'La réponse doit être 200 ou 422 selon la version de Symfony'
        );
        $this->assertSelectorExists('form[name="register"]');
        $this->assertSelectorTextContains('.invalid-feedback', $expectedErrorMessage);
        //$this->assertSelectorExists('.invalid-feedback');
    }

        public function provideInvalidRegisterData(): \Generator
    {
        yield 'empty name' => [['register[name]' => ''],'Ce champ doit être renseigné',];
        yield 'empty address' => [['register[address]' => ''],'Ce champ doit être renseigné',];
        yield 'empty town' => [['register[town]' => ''],'Ce champ doit être renseigné',];
        yield 'empty email' => [['register[email]' => ''],'Ce champ doit être renseigné',];
        yield 'invalid email' => [['register[email]' => 'not-an-email'],'L\'Email que vous avez renseigné n\'est pas valide',];
        yield 'invalid phone' => [['register[phone]' => 'abc123'],'Le numéro de téléphone n\'est pas valide.',];
        yield 'empty status' => [['register[status]' => ''],'Ce champ doit être renseigné',];
        yield 'empty firstName' => [['register[firstName]' => ''],'Ce champ doit être renseigné',];
        yield 'empty lastName' => [['register[lastName]' => ''],'Ce champ doit être renseigné',];
        yield 'empty password' => [['register[plainPassword]' => ''],'Ce champ doit être renseigné',];
        yield 'uniq email' => [['register[email]' => 'orgatest@email.com'],'Cet email est déjà utilisé.',];

    }

    /**
     * @dataProvider provideAccessData
     */
    public function testAccessToProtectedPages(?string $email, string $path, string $method, string $codeHttp):void
    {
        if ($email !== null){
        $organisationRepository = $this->em->getRepository(Organisation::class);
        $organisation = $organisationRepository->findOneBy(['email' => $email]);
        $this->client->loginUser($organisation);
        }

        $this->client->catchExceptions(true);
        $crawler = $this->client->request($method, $path);

        if ($email === null){
            $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
            return;
        } 

        $this->assertResponseStatusCodeSame(constant(Response::class . '::' . $codeHttp));
            
        // if ($codeHttp === 'HTTP_OK' && $path === '/organisation'){
        //     //dump($email, $path);
        //     $organisationName = $organisation->getName();
        //     $this->assertSelectorTextContains('h1', $organisationName);
        // }

        if ($codeHttp === 'HTTP_OK' && $path === '/organisation/update'){
            //dump($email, $path);
            $emailValue = $crawler->filter('#register_email')->attr('value');
            $this->assertSame($email, $emailValue);
        }
    }

    public function provideAccessData(): \Generator
    {
        // /organisation
        yield 'visitor_orga' => [null, '/organisation', 'GET', 'HTTP_FORBIDDEN'];
        yield 'organisation_orga' => ['orgatest@email.com', '/organisation', 'GET', 'HTTP_OK'];
        yield 'admin_orga' => ['admin@ubayeagenda.com', '/organisation', 'GET', 'HTTP_FORBIDDEN'];
        
        // /organisation/update
        yield 'visitor_orga_update' => [null, '/organisation/update', 'GET', 'HTTP_FORBIDDEN'];
        yield 'organisation_orga_update' => ['orgatest@email.com', '/organisation/update', 'GET', 'HTTP_OK'];
        yield 'admin_orga_update' => ['admin@ubayeagenda.com', '/organisation/update', 'GET', 'HTTP_FORBIDDEN'];

        // /organisation/delete
        yield 'visitor_orga_delete' => [null, '/organisation/delete', 'POST', 'HTTP_FORBIDDEN'];
        yield 'organisation_orga_delete' => ['orgatest@email.com', '/organisation/delete', 'POST', 'HTTP_FOUND'];
        yield 'admin_orga_delete' => ['admin@ubayeagenda.com', '/organisation/delete', 'POST', 'HTTP_FORBIDDEN'];

        // /admin/organisations
        yield 'visitor_admin_organisations' => [null, '/admin/organisations', 'GET', 'HTTP_FORBIDDEN'];
        yield 'organisation_admin_organisaTIons' => ['orgatest@email.com', '/admin/organisations', 'GET', 'HTTP_FORBIDDEN'];
        yield 'admin_admin_organisatIons' => ['admin@ubayeagenda.com', '/admin/organisations', 'GET', 'HTTP_OK'];

        // /admin/events
        yield 'visitor_admin_events' => [null, '/admin/events', 'GET', 'HTTP_FORBIDDEN'];
        yield 'organisation_admin_events' => ['orgatest@email.com', '/admin/events', 'GET', 'HTTP_FORBIDDEN'];
        yield 'admin_admin_events' => ['admin@ubayeagenda.com', '/admin/events', 'GET', 'HTTP_OK'];
    }

    public function testOrganisationUpdateIsSuccessful():void
    {
        $orgaTestEmail = "orgatest@email.com"; 
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $orgaTestEmail]);
        $this->client->loginUser($organisation);

        $crawler = $this->client->request('GET', '/organisation/update');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->filter('form[name="register"]')->form([ 
            'register[name]' => "OrgaTest",
            'register[address]' => "UpdatedAddress",
            'register[town]' => '3',
            'register[email]' => 'orgatest@email.com',
            'register[phone]' => "0123456789",
            'register[status]' => "0",
            'register[firstName]' => "NewOrga",
            'register[lastName]' => "NEWORGA",
            'register[plainPassword]' => "password",
            // 'charter' => 'on',
        ]);

            $this->client->submit($form);
            $this->assertResponseRedirects('/', Response::HTTP_FOUND);
            $this->client->followRedirect();

            $organisationUpdated = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $orgaTestEmail]);
            $this->AssertEquals('UpdatedAddress', $organisationUpdated->getAddress());
    }

    public function testOrganisationDeleteIsSuccessful():void
    {
        $orgaToDeleteEmail = "orgatodelete@email.com"; 
        $organisationRepository = $this->em->getRepository(Organisation::class);
        $organisation = $organisationRepository->findOneBy(['email' => $orgaToDeleteEmail]);
        $organisationName = $organisation->getName();
        $this->client->loginUser($organisation);


        $orgaToDeleteEmail = "orgatodelete@email.com";     
        $orgaToDelete = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $orgaToDeleteEmail]);

        $this->client->loginUser($orgaToDelete);

        $crawler = $this->client->request('GET', '/');
        dump($crawler);

        $eventsToDelete = $this->em->getRepository(Event::class);
        $eventCountBeforeDelete = $eventsToDelete->count(['organisation' => $orgaToDelete]);
        $this->assertGreaterThan(0, $eventCountBeforeDelete);

        $csrfTokenManager = $this->container->get('security.csrf.token_manager');
        $token = $csrfTokenManager->getToken('delete' . $orgaToDelete->getId())->getValue();
    // FAILED error message There is currently no session available.

        // Envoi de la requête POST
        $this->client->request('POST', '/organisation/delete', [
            '_token' => $token,
        ]);

        $this->client->request('POST', '/organisation/delete', ['_token' => $token]);

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertResponseRedirects('/eventlist');
        $this->client->followRedirect();

        $orgaDeleted = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $orgaToDeleteEmail]);

        $this->assertNull($orgaDeleted);

        $eventsDeleted = $this->em->getRepository(Event::class);
        $eventCountAfterDelete = $eventsDeleted->count(['organisation' => $orgaToDelete]);
        $this->assertEquals(0, $eventCountAfterDelete);
    }

    /**
     * @dataProvider provideAdminListPath
     */

    public function testDisplayOfOrganisationAndEventListForAdminIsSuccessful(string $path):void
    {
        $adminEmail = "admin@ubayeagenda.com"; 
        $organisationRepository = $this->em->getRepository(Organisation::class);
        $admin = $organisationRepository->findOneBy(['email' => $adminEmail]);
        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', $path);
        $this->assertResponseIsSuccessful();

        if ($path === '/admin/organisations') {
            $organisations = $this->em->getRepository(Organisation::class)->findByRole('ROLE_ORGANISATION');
            $numberExpected = count($organisations);
            $this->assertSelectorCount($numberExpected, '.orga_name');
        }

        if ($path === '/admin/events') {
            $events = $this->em->getRepository(Event::class)->findAll();
            $numberExpected = count($events);
            $this->assertSelectorCount($numberExpected, '.event_name');
        }
   
    }

    public function provideAdminListPath(): \Generator
    {
        yield 'admin_organisations' => ['/admin/organisations'];
        yield 'admin_events' => ['/admin/events'];
    }

    
    public function testDisplayOfOrganisationPageIsSuccessful():void
    {
        $email = "orgatest@email.com"; 
        $organisationRepository = $this->em->getRepository(Organisation::class);
        $organisation = $organisationRepository->findOneBy(['email' => $email]);
        $this->client->loginUser($organisation);
        $this->client->request('GET', '/organisation');
        $this->assertResponseIsSuccessful();
        $organisationName = $organisation->getName();
        $this->assertSelectorTextContains('h1', $organisationName);


        $events = $this->em->getRepository(Event::class)->findBy(['organisation' => $organisation->getId()]);
        $numberExpected = count($events);
        $this->assertSelectorCount($numberExpected, '.event_name');
   
    }



}

    // public function testDeleteOrganisationWithInvalidCsrfToken(): void
    // {
    //     $client = static::createClient();
    //     $container = static::getContainer();
    //     $entityManager = $container->get('doctrine')->getManager();

    //     $organisation = new Organisation();
    //     $organisation->setEmail('fake@example.com');
    //     $organisation->setPassword('password');
    //     $entityManager->persist($organisation);
    //     $entityManager->flush();

    //     $client->loginUser($organisation);

    //     // Envoi avec un token invalide
    //     $client->request('POST', '/organisation/delete', [
    //         '_token' => 'invalid_token',
    //     ]);

    //     // ✅ Redirection vers la page d’accueil
    //     $this->assertResponseRedirects('/home');

    //     // ✅ Vérifie que l’organisation existe toujours
    //     $stillExists = $entityManager->getRepository(Organisation::class)->find($organisation->getId());
    //     $this->assertNotNull($stillExists);
    // }