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
                           VilleRepository $villeRepository
    ): Response
    {
        $sortie = new Sortie();
        $tableauVille = $villeRepository->findAll();
        $sortieForm = $this->createForm(SortieFormType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            // v??rif si "publier la sortie" est coch??
            if ($request->get('etatcheckbox') !== null) {
                $etat = $etatRepository->find(2); // ??tat = publi??e
            } else {
                $etat = $etatRepository->find(1); // ??tat = cr????e
            }

            // j'hydrate l'objet sortie
            $sortie->setEtat($etat);
            $user = $userRepository->find($this->getUser());
            $sortie->setOrganisateur($user);
            $sortie->setCampus($user->getCampus());


            // r??cup??ration de l'image
            if ($sortieForm->get('image')->getData()) {
                $image = $sortieForm->get('image')->getData();
                $urlImage = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($this->getParameter('image_sortie_directory'), $urlImage);
                $sortie->setUrlImage($urlImage);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Ta sortie a bien ??t?? cr????e !');
            return $this->redirectToRoute('sortie_detail', [
                "id" => $sortie->getId()]);
        }

        //----------- FORMULAIRE DE CREATION DE LIEU (POP-UP) -----------

        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
        $lieuForm->handleRequest($request);


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
                    return $this->redirectToRoute('sortie_annuler', ["id" => $sortie->getId()] );

                } elseif ($editForm->get('delete')->isClicked()) {
                    // clic sur le bouton Supprimer
                    $entityManager->remove($sortie);
                    $entityManager->flush();

                    $this->addFlash('success', 'Ta sortie a bien ??t?? supprim??e !');
                    return $this->redirectToRoute('main');

                } else { // -> clic sur le bouton Enregistrer
                    // v??rif si "publier la sortie" est coch??
                    if ($request->get('etatcheckbox') !== null) {
                        $etat = $etatRepository->find(2); // ??tat = publi??e
                    } else {
                        $etat = $etatRepository->find(1); // ??tat = cr????e
                    }
                    $sortie->setEtat($etat);
                    $entityManager->flush();

                    $this->addFlash('success', 'Ta sortie a bien ??t?? mise ?? jour !');
                    return $this->redirectToRoute('sortie_detail', ["id" => $sortie->getId()]);
                }
            }

            // --------------- ENREGISTRER NOUVELLE IMAGE -----------------
            if ($editForm->get('image')->getData()) {
                //ces 2 lignes concernent la mise ?? jour de l'image
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

            //---------------------------------------------------------------------------------------

            // envoi des formulaires (avec le formulaire de Sortie rempli) en HTML
            return $this->render('sortie/edit.html.twig', ['sortieForm' => $editForm->createView(), 'lieuForm' => $lieuForm->createView(), 'sortie' => $sortie]);
        }

        // si l'utilisateur n'est pas l'organisateur :
        $this->addFlash('warning', "Tu dois ??tre l'organisateur pour modifier cette sortie");
        return $this->redirectToRoute('sortie_detail', ["id" => $sortie->getId()]);
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
                $this->addFlash('success', 'Votre inscription a bien ??t?? enregistr?? !');
            }
            else {
                $this->addFlash('danger', 'La date limite pour s\'inscrire est d??pass??e. Vous ne pouvez pas vous y inscrire !');
            }
        }
        else {
            $this->addFlash('danger', 'La sortie est compl??te. Vous ne pouvez pas vous y inscrire !');
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
            $this->addFlash('success', 'Votre d??sinscription a bien ??t?? enregistr?? !');
        }
        else {
            $this->addFlash('danger', 'La sortie est termin??e. Vous ne pouvez pas vous y d??sinscrire !');
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
                // Passage ?? l'??tat : annulee(id=6)
                $etat = $etatRepository->find(6);
                $sortie->setEtat($etat);
                $sortie->setDetailAnnulation($raisonAnnulationForm->get('raisonAnnulation')->getData());

                // Envoi d'un email ?? chaque participant pour les avertir de l'annulation
                $participants = $sortie->getParticipants();
                foreach ($participants as $participant) {
                    $this->emailVerifier->sendEmailAnnulationSortie($participant, $user, $sortie);
                    $sortie->removeParticipant($participant);
                }

                $entityManager->flush();
                $this->addFlash('success', 'La sortie a bien ??t?? annul??e !');
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

//    /**
//     * M??thode non utilis??e pour cr??er un formulaire de cr??ation de lieu
//     * @param Request $request
//     * @param LieuRepository $lieuRepository
//     * @return \Symfony\Component\HttpFoundation\RedirectResponse
//     */
//    public function setLieuForm(EntityManagerInterface $entityManager,
//                                Request $request,
//                                LieuRepository $lieuRepository)
//    {
//        $lieu = new Lieu();
//
//        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
//        $lieuForm->handleRequest($request);
//
//        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
//            $entityManager->persist($lieu);
//            $entityManager->flush();
//
//            $nouveauLieu = $lieuRepository->find($lieu->getId());
//
//            return $this->redirectToRoute('sortie_create', ['nouveauLieu' => $nouveauLieu]);
//        }
//
//        return $this->redirectToRoute('sortie_create', [
//            'LieuForm' => $lieuForm->createView(),
//        ]);
//    }

//    /**
//     * @Route("sortie/test", name="sortie_test")
//     * @return Response
//     */
//    public function test(SortieRepository $sortieRepository, EtatRepository $etatRepository)
//    {
//        $etat2 = $etatRepository->find(2);
//        $etat3 = $etatRepository->find(3);
//
//        $tableauSortiesEtat2 = $sortieRepository->findByEtat($etat2);
//        $sortieRepository->etatsUpdate($etat2, $etat3);
//        $tableauSortiesEtat2bis = $sortieRepository->findByEtat($etat2);
//        return $this->render('sortie/testQueries.html.twig', ['tableauSorties' => $tableauSortiesEtat2, 'tableauSorties2' => $tableauSortiesEtat2bis]);
//    }

}
