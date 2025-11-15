<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Organisation;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class EventControllerTest extends AbstractControllerTest
{

    public function testDisplayEventPageIsSuccessful():void
    {
        $eventName = "EventTestName1";
        $eventId = $this->em->getRepository(Event::class)->findOneBy(['name' => $eventName])->getId();
        $this->client->request('GET','/event/event/' . $eventId);
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', $eventName);
    }

// Given : a new organisation create an event
// When : information given complies with the form (good organisation)
// Then : check display of the page
// And : check authentication
// And : check new event has created in the database
// And : check the redirection
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
            // 'event[startDate]' => (new \DateTime())->format('Y-m-d\TH:i'),
            'event[startDate]' => $startDate,
            'event[description]' => 'Blabla',
            'event[poster]' => '',
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

// Given : an new organisation create an bad event
// When : information given are not correct
// Then : check that new event is not created in the database
// And : check error message and redirection

    /**
     * @dataProvider provideInvalidEventData
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
            'event[poster]' => '',
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

// Given : a new organisation update an event
// When : connecting
// Then : check display of the page
// And : check authentication
// And : check new event has created in the database
// And : check the redirection
    public function testUpdateEventIsSuccessful():void
    {
        $email = 'orgatest@email.com';
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $email]);
        $event = $this->em->getRepository(Event::class)->findByOne(['organisation' => $organisation]);
        $eventId = $event->getId();
        $this->client->loginUser($organisation);

        $crawler = $this->client->request('GET', '/organisation/event/add');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="event"]');

    }

// Given : a registered organisation connects to the organisation delete page
// When : connexion
// Then : check display of the page
// And : authentication for user : visitor, organisation, admin
// And : check error message and the redirection
    public function testDeleteEventIsSuccessful():void
    {

    }

    // public function testAccessToAddEventPage():void
    // public function testAccessToUpdateEventPage():void
    // public function testAccessToDeleteEventPage():void




    public function testDisplayOfEventListForAdminIsSuccessful():void
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
