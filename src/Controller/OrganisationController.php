<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Form\OrganisationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrganisationController extends AbstractController
{
    #[Route('/admin/organisations', name: 'organisations')]
    public function organisations(): Response
    {
        return $this->render('organisation/index.html.twig', [
            'controller_name' => 'OrganisationController',
        ]);
    }


    #[Route('/organisation/add', name: 'orga_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $organisation = new Organisation();

        $form = $this->createForm(OrganisationType::class, $organisation)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($organisation);
            $em->flush();
            $this->addFlash('success', 'Votre organisation a bien été enregsitrée !');

            // Back to the previous page
            $referer = $request->headers->get('referer');
            if ($referer) {
                return $this->redirect($referer);
            }

            // Stay on the current page
            return $this->redirectToRoute(
                $request->attributes->get('_route'),
                $request->attributes->get('_route_params')
            );
        }

        return $this->render('/organisation/organisationForm.html.twig', ['form' => $form]);
    }

    #[Route('/organisation/update/{id}', name: 'orga_update')]
    public function updateOrganization(): Response
    {
        return $this->render('organisation/update.html.twig', [
            'controller_name' => 'OrganisationController',
        ]);
    }
     
    #[Route('/admin/organisation/delete/{id}', name: 'admin_orga_delete')]
    public function delete(): Response
    {
        return $this->render('organisation/delete.html.twig', [
            'controller_name' => 'OrganisationController',
        ]);
    }

}
