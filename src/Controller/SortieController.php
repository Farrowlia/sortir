<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SearchSortieFormType;
use App\Form\SortieFormType;
use App\Repository\CommentaireSortieRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Services\SearchSortie;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param EtatRepository $etatRepository
     * @param $userRepository
     * @return Response
     */
    public function create(EntityManagerInterface $entityManager,
                           Request $request,
                           EtatRepository $etatRepository,
                           UserRepository $userRepository,
                           LieuRepository $lieuRepository
    ): Response
    {
        $sortie = new Sortie();
        /*        $sortie->setDateDebut(new \DateTime('now'));*/

        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);

//        if ($request->isMethod('get')) {
//            $lieuId = $request->query->get('id');
//            $lieuSelected = $lieuRepository->find($lieuId);
//            dump($lieuSelected);
//            return new JsonResponse($lieuSelected);
//        }

        /*if ($sortieForm->get('cancel')) {
            return $this->redirectToRoute('main');
        }*/
        if ($sortieForm->isSubmitted() && $sortieForm->isValid())
        {
            /*if ($request->)*/
            //TODO conditions sur l'état : bouton "enregistrer" => etat_id = 1, si bouton "publier" => etat_id = 2
            $etat = $etatRepository->find(1);
            $user = $userRepository->find($this->getUser());
            $sortie->setEtat($etat);
            $sortie->setOrganisateur($user);
            $sortie->setCampus($this->getUser()->getCampus());
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('succes', 'Votre sortie a bien été créée !');
            return $this->redirectToRoute('main'); //TODO
        }

        return $this->render('sortie/create.html.twig', ['sortieForm' => $sortieForm->createView()]);
    }

    /**
     * @Route("/sortie/{id}", name="sortie_detail", requirements={"id"="\d+"})
     */
    public function detail(int $id, SortieRepository $sortieRepository, CommentaireSortieRepository $commentaireSortieRepository, Request $request): Response
    {
        $sortie = $sortieRepository->find($id);
        $commentaires = [];

        // On vérifie si on a une requête Ajax
        if ($request->get('ajax')){
            $commentaires = $commentaireSortieRepository->findAll();
            return new JsonResponse([
                'content' => $this->renderView('sortie/content/_commentaires.html.twig', compact('commentaires'))
            ]);
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'commentaires' => $commentaires,
        ]);
    }
}
