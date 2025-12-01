<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Entity\Organisation;
use Symfony\Component\HttpFoundation\Response;

final class AccessTest extends AbstractControllerTest
{

   /**
     * @dataProvider provideAccessData
     */
    public function testAccessToProtectedPages(?string $email, string $path, string $method, string $codeHttp):void
    {
        if ($email !== null){
        $organisationRepository = $this->em->getRepository(Organisation::class);
        $organisation = $organisationRepository->findOneBy(['email' => $email]);
        $this->client->loginUser($organisation);
        }

        $this->client->catchExceptions(true);
        $this->client->request($method, $path);

        if ($email === null){
            $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
            return;
        } 

        $this->assertResponseStatusCodeSame(constant(Response::class . '::' . $codeHttp));
    }

    public function provideAccessData(): \Generator
    {
        // /organisation
        yield 'user_visitor_to_organisation' => [null, '/organisation', 'GET', 'HTTP_FORBIDDEN'];
        yield 'user_organisation_to_organisation' => ['orgatest@email.com', '/organisation', 'GET', 'HTTP_OK'];
        yield 'user_admin_to_organisation' => ['admin@ubayeagenda.com', '/organisation', 'GET', 'HTTP_FORBIDDEN'];
        
        // /organisation/update
        yield 'user_visitor_to_organisation_update' => [null, '/organisation/update', 'GET', 'HTTP_FORBIDDEN'];
        yield 'user_organisation_to_organisation_update' => ['orgatest@email.com', '/organisation/update', 'GET', 'HTTP_OK'];
        yield 'user_admin_to_organisation_update' => ['admin@ubayeagenda.com', '/organisation/update', 'GET', 'HTTP_FORBIDDEN'];

        // /organisation/delete
        yield 'user_visitor_to_organisation_delete' => [null, '/organisation/delete', 'POST', 'HTTP_FORBIDDEN'];
        yield 'user_organisation_to_organisation_delete' => ['orgatest@email.com', '/organisation/delete', 'POST', 'HTTP_FOUND'];
        yield 'user_admin_to_organisation_delete' => ['admin@ubayeagenda.com', '/organisation/delete', 'POST', 'HTTP_FORBIDDEN'];

        // // /organisation/update
        // yield 'user_visitor_to_organisation_update' => [null, '/admin/organisation/update', 'GET', 'HTTP_FORBIDDEN'];
        // yield 'user_organisation_to_organisation_update' => ['orgatest@email.com', '/admin/organisation/update', 'GET', 'HTTP_FORBIDDEN'];
        // yield 'user_admin_to_organisation_update' => ['admin@ubayeagenda.com', '/admin/organisation/update', 'GET', 'HTTP_OK'];

        // // /organisation/delete
        // yield 'user_visitor_to_organisation_delete' => [null, '/admin/organisation/update, 'POST', 'HTTP_FORBIDDEN'];
        // yield 'user_organisation_to_organisation_delete' => ['orgatest@email.com', '/admin/organisation/delete', 'POST', 'HTTP_FORBIDDEN'];
        // yield 'user_admin_to_organisation_delete' => ['admin@ubayeagenda.com', '/admin/organisation/delete', 'POST', 'HTTP_FOUND'];

        // /admin/organisations
        yield 'user_visitor_to_admin_organisations' => [null, '/admin/organisations', 'GET', 'HTTP_FORBIDDEN'];
        yield 'user_organisation_to_admin_organisaTIons' => ['orgatest@email.com', '/admin/organisations', 'GET', 'HTTP_FORBIDDEN'];
        yield 'user_admin_to_admin_organisatIons' => ['admin@ubayeagenda.com', '/admin/organisations', 'GET', 'HTTP_OK'];

        // /admin/events
        yield 'user_visitor_to_admin_events' => [null, '/admin/events', 'GET', 'HTTP_FORBIDDEN'];
        yield 'user_organisation_to_admin_events' => ['orgatest@email.com', '/admin/events', 'GET', 'HTTP_FORBIDDEN'];
        yield 'user_admin_to_admin_events' => ['admin@ubayeagenda.com', '/admin/events', 'GET', 'HTTP_OK'];
    }


    /**
     * @dataProvider provideAuthorizedAccessToEventPages
     */
    public function testAuthorizedAccesToEventPagesIsSuccessful(string $email, string $path, string $codeHttp): void
    {
        $event = $this->em->getRepository(Event::class)->findOneBy(['name' => 'EventTestName1']);
        $eventId = $event->getId();

        $connectedUser = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $email]);
        $this->client->loginUser($connectedUser);

        //$this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);

        $crawler = $this->client->request('GET', $path . $eventId);
        dump( $crawler);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="event"]');

        $this->assertResponseStatusCodeSame(constant(Response::class . '::' . $codeHttp));

    }

    public function provideAuthorizedAccessToEventPages(): \Generator
    {
        // Organisation owner of the event
        //yield 'organisation_event_add' => ['orgatest@email.com', '/organisation/event/add', 'HTTP_FOUND'];
        yield 'organisation_event_update' => ['orgatest@email.com', '/organisation/event/update/', 'HTTP_OK'];
        //yield 'organisation_event_delete' => ['orgatest@email.com', '/organisation/event/delete/', 'HTTP_FOUND'];

        // Admin
        //yield 'admin_event_add' => ['admin@ubayeagenda.com', '/admin/event/add', 'HTTP_FOUND'];
        //yield 'admin_event_update' => ['admin@ubayeagenda.com', '/admin/event/update/', 'HTTP_OK'];
        //yield 'admin_event_delete' => ['admin@ubayeagenda.com', '/admin/event/delete', 'HTTP_FOUND'];
    }

        /**
     * @dataProvider provideNotAuthorizedAccessToEventPages
     */
    public function testNotauthorizedAccessToEventPagesFailed(string $email, string $path, string $codeHttp): void
    {
        $event = $this->em->getRepository(Event::class)->findOneBy(['name' => 'EventTestName1']);
        $eventId = $event->getId();

        $connectedUser = $this->em->getRepository(Organisation::class)->findOneBy(['email' => $email]);
        $this->client->loginUser($connectedUser);

        //$this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);

        $crawler = $this->client->request('GET', $path . $eventId);
        // dump( $crawler);
        // $this->assertResponseIsSuccessful();
        // $this->assertSelectorExists('form[name="event"]');
$this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
        //$this->assertResponseStatusCodeSame(constant(Response::class . '::' . $codeHttp));
 $this->assertResponseStatusCodeSame(403);
    }

        public function provideNotAuthorizedAccessToEventPages(): \Generator
    {
        // Organisation not owner of the event
        //yield 'organisation_event_add' => ['orgatest2@email.com', '/organisation/event/add', 'HTTP_FOUND'];
        yield 'wrong_organisation_event_update' => ['orgatest2@email.com', '/organisation/event/update/', 'HTTP_FORBIDDEN'];
        //yield 'wrong_organisation_event_delete' => ['orgatest2@email.com', '/organisation/event/delete/', 'HTTP_FOUND'];
    }
}