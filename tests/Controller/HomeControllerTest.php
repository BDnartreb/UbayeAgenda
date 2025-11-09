<?php

namespace App\Tests\Controller;

final class HomeControllerTest extends ControllerTestCase
{
    public function testIndex(): void
    {
        $crawler = $this->get('/');

        self::assertResponseIsSuccessful();
        $this->assertSelectorExists('h1'); // exemple
    }

    public function testListEvents(): void
    {
        $crawler = $this->get('/');

        self::assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'EventName5'); // adapte selon ton fixture
    }
}
