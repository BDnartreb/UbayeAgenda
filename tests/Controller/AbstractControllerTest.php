<?php

namespace App\Tests\Controller;

use App\Entity\Organisation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected ParameterBagInterface $params;
    private array $entitiesToRemove = [];

    protected function setUp(): void
    {
        //parent::setUp();
        self::ensureKernelShutdown(); // shutdown the kernel before creating a new client
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get(EntityManagerInterface::class);


        // Accès aux paramètres et services depuis le conteneur
        $container = static::getContainer();
        $this->params = $container->get(ParameterBagInterface::class);
    }

    /**
     * Méthode utilitaire pour récupérer un utilisateur existant
     */
    protected function getOrganisation(string $email)
    {
        $container = static::getContainer();
        $userRepo = $container->get('doctrine')->getRepository(\App\Entity\Organisation::class);

        return $userRepo->findOneBy(['email' => $email]);
    }

    /**
     * Méthode utilitaire pour simuler la connexion d'un utilisateur
     */
    protected function loginOrganisationByEmail(string $email): void
    {
        $organisation = $this->getOrganisation($email);
        $this->client->loginUser($organisation);
    }

    /**
     * @param array<string, string> $parameters
     */
    protected function get(string $uri, array $parameters = []): Crawler
    {
        return $this->client->request('GET', $uri, $parameters);
    }

    protected function login(string $email = 'orgatest@email.com'): void
    {
        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $email]);
        if ($organisation) {
            $this->client->loginUser($organisation);
        }
    }

    /**
     * Ajoute une entité à supprimer automatiquement à la fin du test
     */
    protected function scheduleForRemoval(object $entity): void
    {
        $this->entitiesToRemove[] = $entity;
    }

    protected function tearDown(): void
    {
        // Supprimer toutes les entités enregistrées
        foreach ($this->entitiesToRemove as $entity) {
            $this->em->remove($entity);
        }
        $this->em->flush();

        parent::tearDown();

        $this->em->close();
        //$this->em = null;
    }

}
