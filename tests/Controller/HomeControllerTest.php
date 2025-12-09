<?php

namespace App\Tests\Controller;

final class HomeControllerTest extends AbstractControllerTest
{

    public function testDisplayEventListPage():void
    {
        $this->client->request('GET','/');
        $this->assertResponseRedirects('/eventlist');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(8, '.event');
    }

    public function testDisplayPostersPage():void
    {
        $this->client->request('GET','/posters');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.event-poster');
    }

    public function testDisplayContactPage():void
    {
        $this->client->request('GET','/contact');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    public function testDisplayCharterPage():void
    {
        $this->client->request('GET','/charter');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    public function testDisplayUsermanualPage():void
    {
        $this->client->request('GET','/usermanual');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }
}
