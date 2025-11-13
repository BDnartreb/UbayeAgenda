<?php

namespace App\Tests\Controller;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SecurityControllerTest extends AbstractControllerTest
{

    public function testLoginPageIsDisplayed():void
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');
        $this->assertSelectorExists('form');
    }

    public function testLoginIsSuccessfull():void
    {
        //$this->loginOrganisationByEmail('orgatest@email.com');

        $crawler = $this->client->request('GET', '/login');
                $form = $crawler->selectButton('Connexion')->form([
            '_username' => 'orgatest@email.com',
            '_password' => 'password',
        ]);

        $this->client->submit($form);
        $this->client->request('GET', '/login');
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertResponseRedirects('/eventlist');
        $this->client->followRedirect();
        // dump($this->client->getResponse()->getContent());
        $this->assertSelectorExists('h1');

        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = static::getContainer()->get(TokenStorageInterface::class);
        /** @var TokenInterface|null $token */
        $token = $tokenStorage->getToken();
        $this->assertNotNull($token, 'Le token d’authentification ne doit pas être nul');
        $this->assertInstanceOf(\App\Entity\Organisation::class, $token->getUser());
        $this->assertSame('orgatest@email.com', $token->getUserIdentifier());
    }

    /**
    * 
    * @dataProvider provideBadConnectionParams
    */
     public function testLoginShouldFailed(string $email, string $password):void
    {
        $crawler = $this->client->request('GET', '/login');
                $form = $crawler->selectButton('Connexion')->form([
            '_username' => $email,
            '_password' => $password,
        ]);

        $this->client->submit($form);
        $authorizationChecker = self::getContainer()->get(AuthorizationCheckerInterface::class);
        self::assertFalse($authorizationChecker->isGranted('IS_AUTHENTICATED'));
        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
    }

    /**
     * @return array<array{string, string}>
     */
    
    public function provideBadConnectionParams(){
        return [
            ["baduser@email.com", "password"],
            ["orgatest@email.com", "badpassword"],
        ];
    }

}
