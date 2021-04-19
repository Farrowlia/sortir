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
use App\Security\EmailVerifier;
use App\Services\SearchSortie;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

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
        if ($request->get('ajax') && isset($request->get('sortie_form')['ville'])){

            $tableauLieu = $lieuRepository->findBy(array('ville' => $request->get('sortie_form')['ville']), array('nom' => 'ASC'), null, 0);

            return new JsonResponse([
                'content' => $this->renderView('sortie/content/_selectLieu.html.twig', compact('tableauLieu'))]);
        }

        if ($request->get('ajax') && isset($request->get('sortie_form')['lieu'])){

            $lieu = $lieuRepository->find($request->get('sortie_form')['lieu']);

            return new JsonResponse([
                'content' => $this->renderView('sortie/content/_detailLieu.html.twig', compact('lieu'))]);
        }

        if ($sortieForm->isSubmitted() && $sortieForm->isValid())
        {
            dd('test');
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

//            $lieu = $lieuRepository->find($request->get('selectLieu'));
//            $sortie->setLieu($lieu);

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

        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid())
        {
            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('succes', 'Votre lieu a bien été créée !');
            return $this->redirectToRoute('sortie_create');
        }

        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'lieuForm' => $lieuForm->createView(),
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

    /**
     * @Route("/sortie/{id}/inscription", name="sortie_inscription", requirements={"id"="\d+"})
     */
    public function inscriptionSortie(int $id, SortieRepository $sortieRepository, CommentaireSortieRepository $commentaireSortieRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $userRepository->find($this->getUser());
        $sortie->addParticipant($user);
        $entityManager->flush();

        $sortie = $sortieRepository->find($id);
        $commentaires = $commentaireSortieRepository->findBy(array('sortie' => $id), array('date' => 'DESC'), null, 0);

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'commentaires' => $commentaires,
        ]);
    }

    /**
     * @Route("/sortie/{id}/desinscription", name="sortie_desinscription", requirements={"id"="\d+"})
     */
    public function desinscriptionSortie(int $id, SortieRepository $sortieRepository, CommentaireSortieRepository $commentaireSortieRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $userRepository->find($this->getUser());
        $sortie->removeParticipant($user);
        $entityManager->flush();

        $sortie = $sortieRepository->find($id);
        $commentaires = $commentaireSortieRepository->findBy(array('sortie' => $id), array('date' => 'DESC'), null, 0);

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'commentaires' => $commentaires,
        ]);
    }

    /**
     * @Route("/sortie/{id}/annuler", name="sortie_annuler", requirements={"id"="\d+"})
     */
    public function annulerSortie(int $id, SortieRepository $sortieRepository, CommentaireSortieRepository $commentaireSortieRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request, EtatRepository $etatRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $userRepository->find($this->getUser());
        $raisonAnnulation = 'Je ne suis plus intéressé par cette sortie';

        if ($sortie->getOrganisateur() === $user || $user->getAdministrateur()) {
            // Passage à l'état : annulee(id=6)
            $etat = $etatRepository->find(6);
            $sortie->setEtat($etat);

            $participants = $sortie->getParticipants();
            foreach ($participants as $participant) {
                $this->emailVerifier->sendEmailAnnulationSortie($participant, $user, $sortie, $raisonAnnulation);
                $sortie->removeParticipant($participant);
            }

            $entityManager->flush();
            $this->addFlash('succes', 'La sortie a bien été annulée !');
        }

        return $this->redirectToRoute('main');
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
