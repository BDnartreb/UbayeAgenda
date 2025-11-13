<?php

namespace App\Tests\Controller;

final class HomeControllerTest extends AbstractControllerTest
{

    public function testDisplayOfEventListPage():void
    {
        $this->client->request('GET','/');
        $this->assertResponseRedirects('/eventlist');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.day-container');
    }

    public function testDisplayOfPostersPage():void
    {
        $this->client->request('GET','/posters');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.event-poster');
    }

    public function testDisplayOfContactPage():void
    {
        $this->client->request('GET','/contact');
        $this->assertResponseIsSuccessful();
    }

    public function testDisplayOfCharterPage():void
    {
        $this->client->request('GET','/charter');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Charte d\'utilisation du site');
    }
}
