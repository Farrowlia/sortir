<?php

namespace App\Controller;

use App\Entity\CommentaireSortie;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\LieuFormType;
use App\Form\RaisonAnnulationFormType;
use App\Form\SearchSortieFormType;
use App\Form\SortieFormType;
use App\Repository\CommentaireSortieRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Security\EmailVerifier;
use App\Services\RaisonAnnulation;
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

        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid())
        {
            $entityManager->persist($lieu);
            $entityManager->flush();

//              PAS POSSIBLE POUR L'INSTANT
//            // récupération dans la BDD du lieu tout juste créé
//            $nouveauLieu = $lieuRepository->find($lieu->getId());
//            //$sortie->setLieu($nouveauLieu);

        }
        //----------- FIN DU FORMULAIRE DE CREATION DE LIEU ---------------------

        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);


        // -------------------- REQUETES AJAX POUR AFFICHER SELECT LIEU ET DETAIL LIEU
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
        //---------------------------------------------------------------------------------------

        if ($sortieForm->isSubmitted() && $sortieForm->isValid())
        {
            // vérif si "publier la sortie" est coché
            dump($request->get('etatcheckbox'));
            if ($request->get('etatcheckbox') !== null) {
                $etat = $etatRepository->find(2); // état = publiée
            } else {
                $etat = $etatRepository->find(1); // état = créée
            }

            // j'hydrate l'objet sortie
            $sortie->setEtat($etat);
            $user = $userRepository->find($this->getUser());
            $sortie->setOrganisateur($user);
            $sortie->setCampus($user->getCampus());

//            $lieu = $lieuRepository->find($request->get('selectLieu'));
//            $sortie->setLieu($lieu);

            // récupération de l'image
            if ($sortieForm->get('image')->getData()) {
                //ces 2 lignes concernent la mise à jour de l'image
//                if ($sortie->getUrlImage()) {
//                    unlink($this->getParameter('image_sortie_directory') . '/' . $sortie->getUrlImage());
//                }

                $image = $sortieForm->get('image')->getData();
                $urlImage = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($this->getParameter('image_sortie_directory'), $urlImage);
                $sortie->setUrlImage($urlImage);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('succes', 'Votre sortie a bien été créée !');
            return $this->redirectToRoute('sortie_edit', [
                "id" => $sortie->getId()]);
        }



        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'lieuForm' => $lieuForm->createView(),
            'tableauVille' => $tableauVille
        ]);
    }

    /**
     * MODIFIER UNE SORTIE
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

        $editForm = $this->createForm(SortieFormType::class, $sortie);
        $editForm->handleRequest($request);

//        $ville = $sortie->getLieu()->getVille();
//        dump('controller sortie_edit = '.$ville->getId());
//        $editForm->get('ville')->setData($ville->getId());

        //----------- FORMULAIRE DE CREATION DE LIEU -----------

        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid())
        {
            $entityManager->persist($lieu);
            $entityManager->flush();

        }
        //----------- FIN DU FORMULAIRE DE CREATION DE LIEU ---------------------

        $editForm = $this->createForm(SortieFormType::class, $sortie);
        $editForm->handleRequest($request);

        // -------------------- REQUETES AJAX POUR AFFICHER SELECT LIEU ET DETAIL LIEU ------------------------------
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
        //---------------------------------------------------------------------------------------

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('succes', 'Votre sortie a bien été mise à jour !');
            return $this->redirectToRoute('main'); //TODO
        }

        return $this->render('sortie/edit.html.twig', ['sortieForm' => $editForm->createView(), 'lieuForm' => $lieuForm->createView(),'sortie' => $sortie]);
    }


    /**
     * AFFICHER UNE SORTIE
     * @Route("/sortie/{id}", name="sortie_detail", requirements={"id"="\d+"})
     */
    public function detail(int $id, SortieRepository $sortieRepository, CommentaireSortieRepository $commentaireSortieRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $sortie = $sortieRepository->find($id);
        $commentaires = $commentaireSortieRepository->findBy(array('sortie' => $id), array('date' => 'DESC'), null, 0);

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
        if (count($sortie->getParticipants()) < $sortie->getNbreInscriptionMax()) {
            if ($sortie->getDateCloture() > new \DateTime()) {
                $user = $userRepository->find($this->getUser());
                $sortie->addParticipant($user);
                $entityManager->flush();
                $this->addFlash('success', 'Votre inscription a bien été enregistré !');
            }
            else {
                $this->addFlash('danger', 'La date limite pour s\'inscrire est dépassée. Vous ne pouvez pas vous y inscrire !');
            }
        }
        else {
            $this->addFlash('danger', 'La sortie est complète. Vous ne pouvez pas vous y inscrire !');
        }

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
        if ($sortie->getDateDebut() > new \DateTime()) {
            $user = $userRepository->find($this->getUser());
            $sortie->removeParticipant($user);
            $entityManager->flush();
            $this->addFlash('success', 'Votre désinscription a bien été enregistré !');
        }
        else {
            $this->addFlash('danger', 'La sortie est terminée. Vous ne pouvez pas vous y désinscrire !');
        }

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

        $raisonAnnulation = new RaisonAnnulation();
        $raisonAnnulationForm = $this->createForm(RaisonAnnulationFormType::class, $raisonAnnulation);
        $raisonAnnulationForm->handleRequest($request);

        if ($sortie->getOrganisateur() === $user || $user->getAdministrateur()) {
            if ($raisonAnnulationForm->isSubmitted() && $raisonAnnulationForm->isValid()) {
                // Passage à l'état : annulee(id=6)
                $etat = $etatRepository->find(6);
                $sortie->setEtat($etat);
                $sortie->setDetailAnnulation($raisonAnnulationForm->get('raisonAnnulation')->getData());

                // Envoi d'un email à chaque participant pour les avertir de l'annulation
                $participants = $sortie->getParticipants();
                foreach ($participants as $participant) {
                    $this->emailVerifier->sendEmailAnnulationSortie($participant, $user, $sortie);
                    $sortie->removeParticipant($participant);
                }

                $entityManager->flush();
                $this->addFlash('success', 'La sortie a bien été annulée !');
                return $this->redirectToRoute('main');
            }
        }
        else {
            return $this->redirectToRoute('main');
        }

        return $this->render('sortie/annuler.html.twig', [
            'raisonAnnulationForm' => $raisonAnnulationForm->createView(),
        ]);
    }

    /**
     * Méthode non utilisée pour créer un formulaire de création de lieu
     * @param Request $request
     * @param LieuRepository $lieuRepository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
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
