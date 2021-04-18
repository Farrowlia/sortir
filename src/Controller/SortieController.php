<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\LieuFormType;
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
     * @param Request $request
     * @param SortieRepository $sortieRepository
     * @return Response
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
     * @param UserRepository $userRepository
     * @param LieuRepository $lieuRepository
     * @param Lieu|null $lieu
     * @return Response
     */
    public function create(EntityManagerInterface $entityManager,
                           Request $request,
                           EtatRepository $etatRepository,
                           UserRepository $userRepository,
                           LieuRepository $lieuRepository,
                           ?Lieu $lieu
    ): Response
    {
        $sortie = new Sortie();
        $etat = new Etat();
        /*        $sortie->setDateDebut(new \DateTime('now'));*/




        //----------- FORMULAIRE DE CREATION DE LIEU -----------
        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid())
        {
            $entityManager->persist($lieu);
            $entityManager->flush();

            $nouveauLieu = $lieuRepository->find($lieu->getId());

            $sortie->setLieu($nouveauLieu);
        }


        //----------- FIN DU FORMULAIRE DE CREATION DE LIEU ---------------------

        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);


//        if ($request->isMethod('get')) {
//            $lieuId = $request->query->get('id');
//            $lieuSelected = $lieuRepository->find($lieuId);
//            dump($lieuSelected);
//            return new JsonResponse($lieuSelected);
//        }


        if ($sortieForm->isSubmitted() && $sortieForm->isValid())
        {
            // set de l'état en fonction du bouton choisi (enregistrer ou publier)
            if ($sortieForm->get('save')->isClicked()) {
                dump('save is clicked');
                $etat = $etatRepository->find(1); // état = créée
            }
            if ($sortieForm->get('saveAndPublish')->isClicked()) {
                dump('saveAndPublish is clicked');
                $etat = $etatRepository->find(2); // état = publiée

            }
            // récupération et injection du nom de l'organisateur
            $user = $userRepository->find($this->getUser());
            $sortie->setOrganisateur($user);
            //récupération et injection de son campus
            $sortie->setCampus($this->getUser()->getCampus());

            // injection de l'état défini plus haut
            $sortie->setEtat($etat);

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('succes', 'Votre sortie a bien été créée !');
            return $this->redirectToRoute('main'); //TODO
        }

        return $this->render('sortie/create.html.twig', ['sortieForm' => $sortieForm->createView(), 'lieuForm' => $lieuForm->createView()]);
    }

    /**
     * @Route("/sortie/{id}", name="sortie_detail", requirements={"id"="\d+"})
     */
    public function detail(int $id, SortieRepository $sortieRepository, CommentaireSortieRepository $commentaireSortieRepository, Request $request): Response
    {
        $sortie = $sortieRepository->find($id);
        $commentaires = [];
            dump('test back', $request->get('ajax'));

        // On vérifie si on a une requête Ajax
        if ($request->get('ajax')){
            $commentaires = $commentaireSortieRepository->findBy([$id]);
            return new JsonResponse([
                'content' => $this->renderView('sortie/content/_commentaires.html.twig', compact('commentaires'))
            ]);
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'commentaires' => $commentaires,
        ]);
    }

    /**
     * @Route("/sortie/{id}/edit", name="sortie_edit", requirements={"id"="\d+"})
     */
    public function edit(Sortie $sortie,
                           SortieRepository $sortieRepository,
                           Request $request,
                           LieuRepository $lieuRepository,
                           EntityManagerInterface $entityManager

                    )
    {
//        $sortie = $sortieRepository->find($id);
        dump($sortie);
        $editForm = $this->createForm(SortieFormType::class, $sortie);
        $editForm->handleRequest($request);

        //----------- FORMULAIRE DE CREATION DE LIEU -----------
        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid())
        {
            $entityManager->persist($lieu);
            $entityManager->flush();

            $nouveauLieu = $lieuRepository->find($lieu->getId());

            $sortie->setLieu($nouveauLieu);
        }
        //----------- FIN DU FORMULAIRE DE CREATION DE LIEU ---------------------

        // TODO gérer les états (bouton publier)

        $editForm = $this->createForm(SortieFormType::class, $sortie);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid())
        {
            $entityManager->flush();

            $this->addFlash('succes', 'Votre sortie a bien été mise à jour !');
            return $this->redirectToRoute('main'); //TODO
        }

        return $this->render('sortie/edit.html.twig', ['sortieForm' => $editForm->createView(), 'lieuForm' => $lieuForm->createView(), $sortie]);
    }


}
