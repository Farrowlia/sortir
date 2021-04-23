<?php

namespace App\Controller;

use App\Entity\CommentaireSortie;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\LieuFormType;
use App\Form\RaisonAnnulationFormType;
use App\Form\SearchSortieFormType;
use App\Form\SearchSortieUserFormType;
use App\Form\SortieFormType;
use App\Repository\CommentaireSortieRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Security\EmailVerifier;
use App\Services\RaisonAnnulation;
use App\Services\SearchSortie;
use App\Services\SearchSortieUser;
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

    public function __construct(SortieRepository $sortieRepository,
                                EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
//        $sortieRepository->etatsUpdate();
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
            'tableauSorties' => $tableauSorties,
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
        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            // vérif si "publier la sortie" est coché
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


            // récupération de l'image
            if ($sortieForm->get('image')->getData()) {
                $image = $sortieForm->get('image')->getData();
                $urlImage = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($this->getParameter('image_sortie_directory'), $urlImage);
                $sortie->setUrlImage($urlImage);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Ta sortie a bien été créée !');
            return $this->redirectToRoute('sortie_edit', [
                "id" => $sortie->getId()]);
        }

        //----------- FORMULAIRE DE CREATION DE LIEU (POP-UP) -----------

        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
        $lieuForm->handleRequest($request);

        // -------------------- REQUETES AJAX POUR ENREGISTRER UN LIEU ------------------------------
        if ($request->get('ajax') && $request->get('lieu_form')['nom'])  {

            $lieu->setNom($request->get('lieu_form')['nom']);
            $lieu->setRue($request->get('lieu_form')['rue']);
            $lieu->setVille($request->get('lieu_form')['ville']);
            $lieu->setLatitude($request->get('lieu_form')['latitude']);
            $lieu->setLongitude($request->get('lieu_form')['longitude']);

                $entityManager->persist($lieu);
                $entityManager->flush();

//            $tableauLieu = $lieuRepository->findAll();
//            , compact('tableauLieu')
            return new JsonResponse([
//                'content' => $this->renderView('sortie/content/_selectLieu.html.twig')]);
            'ok']);
        }

//        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
//            $entityManager->persist($lieu);
//            $entityManager->flush();

//              PAS POSSIBLE POUR L'INSTANT
//            // récupération dans la BDD du lieu tout juste créé
//            $nouveauLieu = $lieuRepository->find($lieu->getId());
//            //$sortie->setLieu($nouveauLieu);

//        }

        //---------------------------------------------------------------------------------------

        // envoi du formulaire en HTML
        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'lieuForm' => $lieuForm->createView(),
            'tableauVille' => $tableauVille
        ]);
    }

    /**
     * RECUPERER LES LIEUX PAR VILLE
     * @Route("/sortie/lieux/{id}", name="sortie_get_lieux_by_ville", requirements={"id"="\d+"})
     */
    public function getLieuxByVille(int $id, LieuRepository $lieuRepository)
    {

        $tableauLieu = $lieuRepository->findBy(array('ville' => $id), array('nom' => 'ASC'), null, 0);

        return new JsonResponse([
            'content' => $this->renderView('sortie/content/_selectLieu.html.twig', compact('tableauLieu'))]);

    }

    /**
     * MODIFIER UNE SORTIE
     * @Route("/sortie/{id}/edit", name="sortie_edit", requirements={"id"="\d+"})
     */
    public function edit(Sortie $sortie,
                         SortieRepository $sortieRepository,
                         Request $request,
                         EtatRepository $etatRepository,
                         LieuRepository $lieuRepository,
                         UserRepository $userRepository,
                         EntityManagerInterface $entityManager)
    {

        $user = $userRepository->find($this->getUser());

        if ($sortie->getOrganisateur()->getId() === $user->getId() || $user->getAdministrateur()) {

            //----------- FORMULAIRE DE LA SORTIE -----------

            $editForm = $this->createForm(SortieFormType::class, $sortie);

            $editForm->handleRequest($request);

            if ($editForm->isSubmitted() && $editForm->isValid()) {
                if ($editForm->get('cancel')->isClicked()) {
                    // clic sur le bouton Annuler
                    return $this->redirectToRoute('sortie_annuler', [$sortie->getId()] );

                } elseif ($editForm->get('delete')->isClicked()) {
                    // clic sur le bouton Supprimer
                    $entityManager->remove($sortie);

                    $this->addFlash('success', 'Ta sortie a bien été supprimée !');
                    return $this->redirectToRoute('main');

                } else {
                    // clic sur le bouton Enregistrer
                    // vérif si "publier la sortie" est coché
                    if ($request->get('etatcheckbox') !== null) {
                        $etat = $etatRepository->find(2); // état = publiée
                    } else {
                        $etat = $etatRepository->find(1); // état = créée
                    }
                    $sortie->setEtat($etat);
                    $entityManager->flush();

                    $this->addFlash('success', 'Ta sortie a bien été mise à jour !');
                    return $this->redirectToRoute('sortie_detail', [$sortie->getId()]);
                }
            }

            // --------------- ENREGISTRER NOUVELLE IMAGE -----------------
            if ($editForm->get('image')->getData()) {
                //ces 2 lignes concernent la mise à jour de l'image
                if ($sortie->getUrlImage()) {
                    unlink($this->getParameter('image_sortie_directory') . '/' . $sortie->getUrlImage());
                }

                $image = $editForm->get('image')->getData();
                $urlImage = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($this->getParameter('image_sortie_directory'), $urlImage);
                $sortie->setUrlImage($urlImage);
            }

            //----------- FORMULAIRE DE CREATION DE LIEU (POP-UP) -----------

            $lieu = new Lieu();

            $lieuForm = $this->createForm(LieuFormType::class, $lieu);
            $lieuForm->handleRequest($request);

//            if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
//                dump($lieu);
//                $entityManager->persist($lieu);
//                $entityManager->flush();
//
//            }


            if ($request->get('ajax') && $request->get('lieu_form')['nom'])  {

                $lieu->setNom($request->get('lieu_form')['nom']);
                $lieu->setRue($request->get('lieu_form')['rue']);
                $lieu->setVille($request->get('lieu_form')['ville']);
                $lieu->setLatitude($request->get('lieu_form')['latitude']);
                $lieu->setLongitude($request->get('lieu_form')['longitude']);

                $entityManager->persist($lieu);
                $entityManager->flush();

//            $tableauLieu = $lieuRepository->findAll();
//            , compact('tableauLieu')
                return new JsonResponse([
//                'content' => $this->renderView('sortie/content/_selectLieu.html.twig')]);
                    'ok']);
            }


            //---------------------------------------------------------------------------------------

            // envoi du formulaire en HTML
            return $this->render('sortie/edit.html.twig', ['sortieForm' => $editForm->createView(), 'lieuForm' => $lieuForm->createView(), 'sortie' => $sortie]);
        }
        // si l'utilisateur n'est pas l'organisateur :
        $this->addFlash('warning', "Tu dois être l'organisateur pour modifier cette sortie");
        return $this->redirectToRoute('sortie_detail', $sortie->getId());
    }


    /**
     * AFFICHER UNE SORTIE
     * @Route("/sortie/{id}", name="sortie_detail", requirements={"id"="\d+"})
     */
    public function detail(int $id, SortieRepository $sortieRepository, CommentaireSortieRepository $commentaireSortieRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $sortie = $sortieRepository->find($id);
        $commentaires = $commentaireSortieRepository->findBy(array('sortie' => $id), array('date' => 'DESC'), null, 0);
        $userVisiteur = $userRepository->find($this->getUser());

        if ($request->get('ajax')) {

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
            'tableauParticipants' => $sortie->getParticipants(),
            'commentaires' => $commentaires,
            'userVisiteur' => $userVisiteur,
            'todayMoinsOneMonth' => date_modify(new \DateTime(), '-1 month'),
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

        return $this->redirectToRoute('sortie_detail', ['id' => $id]);
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

        return $this->redirectToRoute('sortie_detail', ['id' => $id]);
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
            return $this->redirectToRoute('sortie_detail', ['id' => $id]);
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
                                LieuRepository $lieuRepository)
    {
        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            $nouveauLieu = $lieuRepository->find($lieu->getId());

            return $this->redirectToRoute('sortie_create', ['nouveauLieu' => $nouveauLieu]);
        }

        return $this->redirectToRoute('sortie_create', [
            'LieuForm' => $lieuForm->createView(),
        ]);
    }

    /**
     * @Route("sortie/test", name="sortie_test")
     * @return Response
     */
    public function test(SortieRepository $sortieRepository, EtatRepository $etatRepository)
    {
        $etat2 = $etatRepository->find(2);
        $etat3 = $etatRepository->find(3);

        $tableauSortiesEtat2 = $sortieRepository->findByEtat($etat2);
        $sortieRepository->etatsUpdate($etat2, $etat3);
        $tableauSortiesEtat2bis = $sortieRepository->findByEtat($etat2);
        return $this->render('sortie/testQueries.html.twig', ['tableauSorties' => $tableauSortiesEtat2, 'tableauSorties2' => $tableauSortiesEtat2bis]);
    }

}
