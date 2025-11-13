<?php

namespace App\Tests\Controller;

use App\Entity\Event;
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
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains('h1', 'Créer un événement');
        dump($crawler);
                
        $form = $crawler->filter('form[name="event"]')->form([
            'event[name]' => 'NewEvent',
            'event[startDate]' => (new \DateTime())->format('Y-m-d\TH:i'),
            'event[description]' => 'Blabla',
            'event[poster]' => '',
            'event[fee]' => '',
            'event[comment]' => '',
            'event[thematic]' => '',
            'event[public]' => '',
            'event[organisation]' => '',
            'event[location]' => '37',
        ]);


        $this->client->submit($form);
        // $eventId = 
        // $this->assertResponseRedirects('/event/event/' . $id, Response::HTTP_FOUND);
        //     $this->client->followRedirect();



    }

// Given : an new organisation create an bad event
// When : information given are not correct
// Then : check that new event is not created in the database
// And : check error message and redirection
    // public function testAddBadEventIsNotSuccessful():void

// Given : a new organisation update an event
// When : connecting
// Then : check display of the page
// And : check authentication
// And : check new event has created in the database
// And : check the redirection
    // public function testUpdateEventIsSuccessful():void

// Given : a registered organisation connects to the organisation delete page
// When : connexion
// Then : check display of the page
// And : authentication for user : visitor, organisation, admin
// And : check error message and the redirection
    // public function testDeleteEventIs Successful():void

    // public function testAccessToAddEventPage():void
    // public function testAccessToUpdateEventPage():void
    // public function testAccessToDeleteEventPage():void





}
