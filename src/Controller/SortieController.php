<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie", name="sortie")
     */
    public function index(): Response
    {
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }

    /**
     * @Route("/sortie/create", name="sortie_create")
     * @return Response
     */
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        $sortie = new Sortie();
        /*$sortie->setOrganisateur($this->getUser()->getPseudo());*/

        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid())
        {
            $entityManager->persist();
            $entityManager->flush();

            $this->addFlash('succes', 'Votre sortie a bien été créée !');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/create.html.twig', ['sortieForm' => $sortieForm->createView()]);
    }

    /**
     * @Route("/sortie/{id}", name="sortie_detail", requirements={"id"="\d+"})
     * @return Response
     */
    public function detail(): Response
    {
        //TODO
    }

}
