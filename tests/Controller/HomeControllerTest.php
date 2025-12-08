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
        $this->assertSelectorCount(8, '.event');
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
        $this->assertSelectorExists('h1');
    }

    public function testDisplayOfCharterPage():void
    {
        $this->client->request('GET','/charter');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    public function testDisplayOfUsermanualPage():void
    {
        $this->client->request('GET','/usermanual');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }
}
