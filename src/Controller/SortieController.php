<?php

namespace App\Controller;

use App\Entity\CommentaireSortie;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\LieuFormType;
use App\Form\SearchSortieFormType;
use App\Form\SortieFormType;
use App\Repository\CommentaireSortieRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
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

        $tableauSorties = $sortieRepository->findSearch($searchSortie);

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
     * @return Response
     */
    public function create(EntityManagerInterface $entityManager,
                           Request $request,
                           EtatRepository $etatRepository,
                           UserRepository $userRepository,
                           LieuRepository $lieuRepository,
                           VilleRepository $villeRepository
    ): Response
    {
        $sortie = new Sortie();
        $tableauVille = $villeRepository->findAll();
        /*        $sortie->setDateDebut(new \DateTime('now'));*/




        //----------- FORMULAIRE DE CREATION DE LIEU -----------
//        $lieu = new Lieu();
//
//        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
//        $lieuForm->handleRequest($request);
//
//        if ($lieuForm->isSubmitted() && $lieuForm->isValid())
//        {
//            $entityManager->persist($lieu);
//            $entityManager->flush();
//
//            $nouveauLieu = $lieuRepository->find($lieu->getId());
//
//            $sortie->setLieu($nouveauLieu);
//        }


        //----------- FIN DU FORMULAIRE DE CREATION DE LIEU ---------------------

        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);


//        if ($request->isMethod('get')) {
//            $lieuId = $request->query->get('id');
//            $lieuSelected = $lieuRepository->find($lieuId);
//            dump($lieuSelected);
//            return new JsonResponse($lieuSelected);
//        }
        if ($request->get('ajax') && $request->get('selectVille')){

            $tableauLieu = $lieuRepository->findBy(array('ville' => $request->get('selectVille')), array('nom' => 'ASC'), null, 0);

            return new JsonResponse([
                'content' => $this->renderView('sortie/content/_selectLieu.html.twig', compact('tableauLieu'))]);
        }

        // ATTENTION la validation doit être fait à la main
        if ($sortieForm->isSubmitted() && $sortieForm->isValid())
        {
//            if ($sortieForm->get('save')->isClicked()) {
//                dump('save is clicked');
//            }
//            if ($sortieForm->get('saveAndPublish')->isClicked()) {
//                dump('saveAndPublish is clicked');
//            }
            //TODO conditions sur l'état : bouton "enregistrer" => etat_id = 1, si bouton "publier" => etat_id = 2
            $etat = $etatRepository->find(1);
            $user = $userRepository->find($this->getUser());
            $sortie->setEtat($etat);
            $sortie->setOrganisateur($user);
            $sortie->setCampus($user->getCampus());

            $lieu = $lieuRepository->find($request->get('selectLieu'));
            $sortie->setLieu($lieu);

            if ($sortieForm->get('image')->getData()) {
                if ($sortie->getUrlImage()) {
                    unlink($this->getParameter('image_sortie_directory') . '/' . $sortie->getUrlImage());
                }

                $image = $sortieForm->get('image')->getData();
                $urlImage = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($this->getParameter('image_sortie_directory'), $urlImage);
                $sortie->setUrlImage($urlImage);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('succes', 'Votre sortie a bien été créée !');
            return $this->redirectToRoute('main'); //TODO
        }

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'tableauVille' => $tableauVille
        ]);
    }

    /**
     * @Route("/sortie/{id}", name="sortie_detail", requirements={"id"="\d+"})
     */
    public function detail(int $id, SortieRepository $sortieRepository, CommentaireSortieRepository $commentaireSortieRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $sortie = $sortieRepository->find($id);
        $commentaires = $commentaireSortieRepository->findBy(array('sortie' => $id), array('date' => 'DESC'), null, 0);
            dump('test back', $request->get('ajax'));

        if ($request->get('ajax')){

            $newCommentaires = new CommentaireSortie();
            $newCommentaires->setSortie($sortie);
            $newCommentaires->setDate(new \DateTime());
            $newCommentaires->setAuteur($userRepository->find($this->getUser()));
            $newCommentaires->setTexte($request->get("inputCommentaireTexte"));
            $entityManager->persist($newCommentaires);
            $entityManager->flush();

            $commentaires = $commentaireSortieRepository->findBy(array('sortie' => $id), array('date' => 'DESC'), null, 0);

            return new JsonResponse([
                'content' => $this->renderView('sortie/content/_commentaires.html.twig', compact('commentaires'))
            ]);
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'commentaires' => $commentaires,
        ]);
    }

    public function setLieuForm(EntityManagerInterface $entityManager,
                                Request $request,
                                LieuRepository $lieuRepository) {
        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid())
        {
            $entityManager->persist($lieu);
            $entityManager->flush();

            $nouveauLieu = $lieuRepository->find($lieu->getId());

            return $this->redirectToRoute('sortie_create', ['nouveauLieu' => $nouveauLieu]);
        }

        return $this->redirectToRoute('sortie_create', [
            'LieuForm' => $lieuForm->createView(),
        ]);
    }

}
