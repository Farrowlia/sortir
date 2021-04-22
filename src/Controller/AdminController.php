<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AdminController extends AbstractController
{
    /**
     * @Route("admin/users", name="gestionUsers")
     */
    public function gestionUsers(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response
    {
        //Affichage de la liste des users
        $tableauUsers = $userRepository->findAll();

        //Si requête ajax 'rechUser' reçue, affichage de la recherche
        if ($request->get('rechUser')) {

            $motRecherche = $request->get('rechUser');

            $result = $userRepository->findByMotRecherche($motRecherche);

            return new JsonResponse([
                'content' => $this->renderView('admin/content/_userSearch.html.twig', compact('result'))
            ]);
        }

        return $this->render('admin/gererLesUsers.html.twig', [
            'users' => $tableauUsers
        ]);
    }

    /**
     * @Route("admin/user/{id}", name="admin_user")
     */
    public function index(int $id, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        return $this->render('admin/adminUserProfil.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("admin/user/{id}/modifier", name="admin_user_modifier")
     */
    public function modifier(int $id, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder): Response
    {

        $user = $userRepository->find($id);

        $userForm = $this->createForm(UserFormType::class, $user);
        $userForm = $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()){

            // on récupère les images de l'user
            if ($userForm->get('image')->getData()) {
                if ($user->getUrlImage()) {
                    unlink($this->getParameter('image_user_directory') . '/' . $user->getUrlImage());
                }

                $image = $userForm->get('image')->getData();
                $urlImage = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($this->getParameter('image_user_directory'), $urlImage);
                $user->setUrlImage($urlImage);
            }

            // encode the password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $userForm->get('plainPassword')->getData()
                )
            );

            $entityManager->flush();

            $this->addFlash('success', 'Le profil a été modifié');

            return $this->redirectToRoute('gestionUsers');
        }

        return $this->render('user/modifier.html.twig', [
            'userForm' => $userForm->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Rendre un utilisateur inactif
     * @Route("admin/user/{id}/disable", name="admin_user_disable")
     */
    public function disable(int $id, UserRepository $userRepository) {

        $entityManager = $this->getDoctrine()->getManager();
        $user = $userRepository->find($id);
        $user->setActif(0);
        $entityManager->flush();

        $this->addFlash('message', 'Utilisateur désactivé avec succès');
        return $this->redirectToRoute('gestionUsers');

    }

    /**
     * Suppression d'un utilisateur en BDD
     * @Route("admin/user/{id}/delete", name="admin_user_delete")
     */
    public function delete(int $id, UserRepository $userRepository):Response {

        $entityManager = $this->getDoctrine()->getManager();
        $user = $userRepository->find($id);
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('message', 'Utilisateur supprimé avec succès');
        return $this->redirectToRoute('gestionUsers');

    }


}
