<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\Organisation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

final class OrganisationControllerTest extends AbstractControllerTest
{
    /**
     * @dataProvider provideAccessToOrganisationAndAdminRouteByVistorData

     */
    public function testAccessToOrganisationAndAdminRouteByVisitorFailed (string $path, string $method):void
    {
        $this->client->catchExceptions(true);
        $this->client->request($method, $path);
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
        // $this->assertSelectorTextContains('button', 'Connexion');
    }

    public function provideAccessToOrganisationAndAdminRouteByVistorData(): \Generator
    {
        yield 'user_visitor_to_organisation' => ['/organisation', 'GET'];
        yield 'user_visitor_to_organisation_update' => ['/organisation/update', 'GET'];
        yield 'user_visitor_to_organisation_delete' => ['/organisation/delete', 'POST'];
        yield 'user_visitor_to_organisation_calendar' => ['/organisation/calendar', 'GET'];
        yield 'user_visitor_to_admin_organisations' => ['/admin/organisations', 'GET'];
        yield 'user_visitor_to_admin_events' => ['/admin/events', 'GET'];     
    }

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
            
            $this->assertResponseRedirects('/logout');
            $this->client->followRedirect();
            $this->assertResponseRedirects('/login');
            $this->client->followRedirect();

            $this->assertSelectorExists('form');
            $this->assertSelectorTextContains('h1', 'Connexion');

            $saved = $this->em->getRepository(Organisation::class)->find($newOrga->getId());
            $this->assertNotNull($saved);
            $this->scheduleForRemoval($saved);
    }

    /**
     * Test of NotBlank and Uniq_Email
     * @dataProvider provideInvalidRegisterData
     * 
     * @param array<string, mixed> $formData Données invalides à injecter dans le formulaire
     * @param string $expectedErrorMessage Message d'erreur attendu
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
     * @dataProvider providerOrganisationUpdateData
     */
    public function testOrganisationUpdateIsSuccessful(string $connectedUserEmail, string $organisationEmail):void
    {
        $connectedUser = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $connectedUserEmail]);
        $this->client->loginUser($connectedUser);

        if ($connectedUserEmail === $organisationEmail){
            $crawler = $this->client->request('GET', '/organisation/update');
            $address = 'updatedByOrgaTest';
        } else {
            $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $organisationEmail]);
            $crawler = $this->client->request('GET', '/organisation/update/' . $organisation->getId());
            $address = 'updatedByAdmin';
        }

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form'); 
        $emailValue = $crawler->filter('#register_email')->attr('value');
        $this->assertSame($organisationEmail, $emailValue);

        $form = $crawler->filter('form[name="register"]')->form([ 
            'register[name]' => "OrgaTest",
            'register[address]' => $address,
            'register[town]' => '3',
            'register[email]' => 'orgatest@email.com',
            'register[phone]' => "0123456789",
            'register[status]' => "0",
            'register[firstName]' => "NewOrgaTestTOTO",
            'register[lastName]' => "NEWORGATEST",
            'register[plainPassword]' => "password",
            // 'charter' => 'on',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/organisation', Response::HTTP_FOUND);
        $this->client->followRedirect();

        $organisationUpdated = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $organisationEmail]);
        $this->AssertEquals($address, $organisationUpdated->getAddress());
    }

    public function providerOrganisationUpdateData(): \Generator
    {
        yield 'organisation_update_by_orgatest' => ['orgatest@email.com', 'orgatest@email.com'];
        yield 'organisation_update_by_admin' => ['admin@ubayeagenda.com', 'orgatest@email.com'];
    }

    // public function testOrganisationDeleteIsSuccessful():void
    // {
    //     $orgaToDeleteEmail = "orgatodelete@email.com"; 
    //     $orgaToDelete = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $orgaToDeleteEmail]);

    //     $session = static::getContainer()->get('session.factory')->createSession();
    //     $cookie = new \Symfony\Component\BrowserKit\Cookie($session->getName(), $session->getId());
    //     $this->client->getCookieJar()->set($cookie);

    //     $this->client->loginUser($orgaToDelete);

    //     $eventsToDelete = $this->em->getRepository(Event::class);
    //     $eventCountBeforeDelete = $eventsToDelete->count(['organisation' => $orgaToDelete]);
    //     $this->assertGreaterThan(0, $eventCountBeforeDelete);

    //     // $csrfTokenManager = $this->container->get('security.csrf.token_manager');

    // $csrfTokenManager = static::getContainer()->get('security.csrf.token_manager');
    // $token = $csrfTokenManager->getToken('delete' . $orgaToDelete->getId())->getValue();
        
    //     // $token = $csrfTokenManager->getToken('delete' . $orgaToDelete->getId())->getValue();
    // // FAILED error message There is currently no session available.

    //     $this->client->request('POST', '/organisation/delete/' . $orgaToDelete->getId(), ['_token' => $token]);

    //     $this->assertResponseRedirects('/');
    //     $this->client->followRedirect();
    //     $this->assertResponseRedirects('/eventlist');
    //     $this->client->followRedirect();

    //     $orgaDeleted = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $orgaToDeleteEmail]);

    //     $this->assertNull($orgaDeleted);

    //     $eventsDeleted = $this->em->getRepository(Event::class);
    //     $eventCountAfterDelete = $eventsDeleted->count(['organisation' => $orgaToDelete]);
    //     $this->assertEquals(0, $eventCountAfterDelete);
    // }

    // public function testAdminDeleteFailed():void
    // {
    //     $adminEmail = "admin@ubayeagenda.com";
    //     $admin = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $adminEmail]);
    //     $this->client->loginUser($admin);
        
    //     $this->client->request('GET', '/organisation');
    //     $csrfTokenManager = $this->container->get('security.csrf.token_manager');
    //     $token = $csrfTokenManager->getToken('delete' . $admin->getId())->getValue();

    //     $this->client->request('POST', '/organisation/delete/' . $admin->getId(), ['_token' => $token]);

    //     $this->assertResponseRedirects('/home', Response::HTTP_OK);
    // }

    public function testDisplayOrganisationListForAdminIsSuccessful():void
    {
        $adminEmail = "admin@ubayeagenda.com";
        $path = "/admin/organisations";
        $organisationRepository = $this->em->getRepository(Organisation::class);
        $admin = $organisationRepository->findOneBy(['email' => $adminEmail]);
        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', $path);
        $this->assertResponseIsSuccessful();

        $organisations = $this->em->getRepository(Organisation::class)->findByRole('ROLE_ORGANISATION');
        $numberExpected = count($organisations);
        $this->assertSelectorCount($numberExpected, '.orga-small-card');
    }
    
    public function testDisplayOrganisationPageIsSuccessful():void
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
        $this->assertSelectorCount($numberExpected, '.event-small-card');
    }

    
    /**
     * @dataProvider provideAccessData
     */
    public function testAccessByOrganisationOrAdminToProtectedRoute(string $email, string $path, string $method, string $codeHttp):void
    {
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $email]);
        $this->client->loginUser($organisation);
        $this->client->catchExceptions(true);
        $this->client->request($method, $path);

        $this->assertResponseStatusCodeSame(constant(Response::class . '::' . $codeHttp));
    }

    public function provideAccessData(): \Generator
    {
        yield 'user_organisation_to_organisation' => ['orgatest@email.com', '/organisation', 'GET', 'HTTP_OK'];
        yield 'user_admin_to_organisation' => ['admin@ubayeagenda.com', '/organisation', 'GET', 'HTTP_OK'];

        yield 'user_organisation_to_organisation_update' => ['orgatest@email.com', '/organisation/update', 'GET', 'HTTP_OK'];
        yield 'user_admin_to_organisation_update' => ['admin@ubayeagenda.com', '/organisation/update', 'GET', 'HTTP_OK'];

        yield 'user_organisation_to_organisation_delete' => ['orgatest@email.com', '/organisation/delete', 'POST', 'HTTP_FOUND'];
//        yield 'user_admin_to_organisation_delete' => ['admin@ubayeagenda.com', '/organisation/delete', 'POST', 'HTTP_FOUND'];

        yield 'user_organisation_to_organisation_calendar' => ['orgatest@email.com', '/organisation/calendar', 'GET', 'HTTP_OK'];
        yield 'user_admin_to_organisation_calendar' => ['admin@ubayeagenda.com', '/organisation/calendar', 'GET', 'HTTP_OK'];

        yield 'user_organisation_to_admin_organisaTIons' => ['orgatest@email.com', '/admin/organisations', 'GET', 'HTTP_FORBIDDEN'];
        yield 'user_admin_to_admin_organisatIons' => ['admin@ubayeagenda.com', '/admin/organisations', 'GET', 'HTTP_OK'];

        yield 'user_organisation_to_admin_events' => ['orgatest@email.com', '/admin/events', 'GET', 'HTTP_FORBIDDEN'];
        yield 'user_admin_to_admin_events' => ['admin@ubayeagenda.com', '/admin/events', 'GET', 'HTTP_OK'];
    }
}
