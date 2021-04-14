<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SearchSortieFormType;
use App\Form\SortieFormType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Services\SearchSortie;
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
    public function index(Request $request, SortieRepository $sortieRepository): Response
    {
        $searchSortie = new SearchSortie();
        $searchSortie->page = $request->get('page', 1);

        $searchSortieFormType = $this->createForm(SearchSortieFormType::class, $searchSortie);
        $searchSortieFormType->handleRequest($request);
//        [$min, $max] = $repository->findMinMax($searchSortie);
        $tableauSorties = $sortieRepository->findSearch($searchSortie);


//        return $this->render('product/index.html.twig', [
//            'products' => $products,
//            'form' => $form->createView(),
//            'min' => $min,
//            'max' => $max
//        ]);


        return $this->render('sortie/index.html.twig', [
            'searchSortieFormType' => $searchSortieFormType->createView(),
            'tableauSorties' => $tableauSorties
        ]);
    }

    /**
     * @Route("/sortie/create", name="sortie_create")
     * @return Response
     */
    public function create(EntityManagerInterface $entityManager,
                           Request $request,
                           EtatRepository $etatRepository
    ): Response
    {
        $sortie = new Sortie();
        /*$sortie->setOrganisateur($this->getUser()->getPseudo());*/

        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid())
        {
            // TODO gérer les états en fonction des règles métier
            $etat = new Etat();
            $etat = $etatRepository->find(2);
            $sortie->setEtat($etat);
            $sortie->setOrganisateur('Hugo'); //TODO
            $sortie->setCampus('Saint-Herblain'); //TODO
            $entityManager->persist($sortie);
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
