<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Organisation;
use Symfony\Component\HttpFoundation\Response;

final class EventControllerTest extends AbstractControllerTest
{
    /**
     * @dataProvider provideAccessToEventData
     */
    public function testAccessToEventIsSuccessful(?string $email): void
    {
        if ($email !== null){
            $connectedUser = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $email]);
            $this->client->loginUser($connectedUser);
        }

        $event = $this->em->getRepository(Event::class)->findOneBy(['name' => 'EventTestName1']);
        $eventId = $event->getId();

        $this->client->request('GET', '/event/event/' . $eventId);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'EventTestName1');
    }
    
    public function provideAccessToEventData(): \Generator
    {
        yield 'user_visitor_to_event' => [null];
        yield 'user_organisation_to_event' => ['orgatest@email.com'];
        yield 'user_admin_to_event' => ['admin@ubayeagenda.com'];
    }

    /**
     * @dataProvider providerEventAddForbiddenToVisitorData
     */
    public function testAccessToEventAddByVisitorFailed(string $path, string $method):void
    {
        $this->client->catchExceptions(true);
        $this->client->request($method, $path);
       // $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function providerEventAddForbiddenToVisitorData(): \Generator
    {
        yield 'user_visitor_to_event_add' => ['/organisation/event/add', 'GET'];
        yield 'user_visitor_to_location_add' => ['/organisation/location/add', 'GET'];
        yield 'user_visitor_to_events' => ['/admin/events', 'GET']; 
    }

        /**
     * @dataProvider providerEventUpdateAndDeleteForbiddenToVisitorData
     */
    public function testAccessEventUpdateAndDeleteByVisitorFailed(string $path, string $method):void
    {
        $eventId = $this->em->getRepository(Event::class)->findOneBy(['name' => 'EventTestName1'])->getId();
        $this->client->catchExceptions(true);
        $this->client->request($method, $path . $eventId);
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function providerEventUpdateAndDeleteForbiddenToVisitorData(): \Generator
    {
        yield 'user_visitor_to_location_update' => ['/organisation/location/update/', 'GET'];
        yield 'user_visitor_to_event_update' => ['/organisation/event/update/', 'GET'];   
        yield 'user_visitor_to_event_delete' => ['/organisation/event/delete/', 'POST']; 
    }

    public function testAddEventIsSuccessful():void
    {
        $email = 'orgatest@email.com';
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $email]);
        $this->client->loginUser($organisation);

        $crawler = $this->client->request('GET', '/organisation/event/add');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="event"]');

        $eventName = 'NewEvent';
        $startDate = new \DateTime();
        $locationId = $this->em->getRepository(Location::class)->findOneBy(['name' => 'LocationTest'])->getId();
                
        $form = $crawler->filter('form[name="event"]')->form([
            'event[name]' => $eventName,
            'event[startDate]' => (new \DateTime())->format('Y-m-d\TH:i'),
            // 'event[startDate]' => $startDate,
            'event[description]' => 'Blabla',
            //'event[poster]' => '',
            'event[fee]' => '2',
            'event[comment]' => '',
            'event[thematic]' => '3',
            'event[public]' => '1',
            'event[location]' => $locationId,
        ]);

        $this->client->submit($form);

        $eventId = $this->em->getRepository(Event::class)->findOneBy(['name' => $eventName])->getId();
        $this->assertResponseRedirects('/event/event/' . $eventId, Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', $eventName);
    }

/**
     * @dataProvider provideInvalidEventData
     * 
     *
     * @param array<string, mixed> $formData Form fields to submit
     * @param string $expectedErrorMessage
    */
    public function testAddEventWithInvalidDataFailed(array $formData, string $expectedErrorMessage):void
        {
        $email = 'orgatest@email.com';
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $email]);
        $this->client->loginUser($organisation);

        $crawler = $this->client->request('GET', '/organisation/event/add');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="event"]');

        $eventName = 'NewEvent';
        $locationId = $this->em->getRepository(Location::class)->findOneBy(['name' => 'LocationTest'])->getId();
               
        $form = $crawler->filter('form[name="event"]')->form(array_merge([
            'event[name]' => $eventName,
            // 'event[startDate]' => (new \DateTime())->format('Y-m-d\TH:i'),
            'event[startDate]' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d\TH:i'),
            'event[description]' => 'Blabla',
            //'event[poster]' => '',
            'event[fee]' => '2',
            'event[comment]' => '',
            'event[thematic]' => '3',
            'event[public]' => '1',
            'event[location]' => $locationId,
        ], $formData));

        //dump($form->getPhpValues());

        $this->client->submit($form);
        $this->assertContains(
            $this->client->getResponse()->getStatusCode(),
            [Response::HTTP_OK, Response::HTTP_UNPROCESSABLE_ENTITY],
            'La réponse doit être 200 ou 422 selon la version de Symfony'
        );
        $this->assertSelectorExists('form[name="event"]');
        $this->assertSelectorTextContains('.invalid-feedback', $expectedErrorMessage);
        //$this->assertSelectorExists('.invalid-feedback');
    }

    public function provideInvalidEventData(): \Generator
    {
        yield 'empty name' => [['event[name]' => ''], 'Ce champ doit être renseigné',];
        yield 'empty startDate' => [['event[startDate]' => ''], 'Ce champ doit être renseigné',];
        yield 'empty fee' => [['event[fee]' => ''], 'Ce champ doit être renseigné',];
        yield 'empty thematic' => [['event[thematic]' => ''], 'Ce champ doit être renseigné',];
        yield 'empty public' => [['event[public]' => ''], 'Ce champ doit être renseigné',];
        yield 'empty location' => [['event[location]' => ''], 'Ce champ doit être renseigné',];
    }


    public function testAddLocationIsSuccessful():void
    {

    }

    public function testAddLocationWithInvalidDataFailed():void
    {

    }

    public function testUpdateLocationIsSuccessful():void
    {

    }

    public function testUpdateLocationWithInvalidDataFailed():void
    {

    }


    /**
     * @dataProvider provideUpdateEventData
     */
    public function testUpdateEventIsSuccessful(string $email, string $path, string $method, string $codeHttp): void
    {
        if ($email !== null){
            $connectedUser = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $email]);
            $this->client->loginUser($connectedUser);
        }

        $event = $this->em->getRepository(Event::class)->findOneBy(['name' => 'EventTestName1']);
        $eventId = $event->getId();

        $crawler = $this->client->request($method, $path . $eventId);
        $this->assertResponseStatusCodeSame(constant(Response::class . '::' . $codeHttp));
        $this->assertSelectorTextContains('h1', 'EventTestName1');
    }

    public function provideUpdateEventData(): \Generator
    {

        yield 'user_organisation_to_event_update' => ['orgatest@email.com', 'GET', '/organisation/event/update/', 'HTTP_FOUND'];
        yield 'user_admin_to_event_update' => ['admin@ubayeagenda.com', 'GET', '/organisation/event/update/', 'HTTP_FOUND'];    
    }

    /**
     * @dataProvider provideDeleteEventData
     */
    public function testDeleteEventIsSuccessful():void
    {

    }

    public function provideDeleteEventData(): \Generator
    {   

        yield 'user_organisation_to_event_delete' => ['orgatest@email.com', '/organisation/event/delete/', 'POST', 'HTTP_FOUND'];
        yield 'user_admin_to_event_delete' => ['admin@ubayeagenda.com', '/organisation/event/delete/', 'POST', 'HTTP_FOUND'];

    }


    public function testEventsForAdminIsSuccessful():void
    {
        $adminEmail = "admin@ubayeagenda.com";
        $path = '/admin/events';
        $organisationRepository = $this->em->getRepository(Organisation::class);
        $admin = $organisationRepository->findOneBy(['email' => $adminEmail]);
        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', $path);
        $this->assertResponseIsSuccessful();

        $events = $this->em->getRepository(Event::class)->findAll();
        $numberExpected = count($events);
        $this->assertSelectorCount($numberExpected, '.event_name');
    }

}
