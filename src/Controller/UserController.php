<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SearchSortieUserFormType;
use App\Form\UserFormType;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Services\SearchSortieUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index(Request $request, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($this->getUser());

        $searchSortieUser = new SearchSortieUser();
        $searchSortieUser->page = $request->get('page', 1);

        $searchSortieUserFormType = $this->createForm(SearchSortieUserFormType::class, $searchSortieUser);
        $searchSortieUserFormType->handleRequest($request);

        $tableauSorties = $sortieRepository->findSearchUser($searchSortieUser, $user);

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'tableauSorties' => $tableauSorties,
            'searchSortieUserFormType' => $searchSortieUserFormType->createView(),
        ]);
    }

    /**
     * @Route("/user/modifier", name="user_modifier")
     */
    public function modifier(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder): Response
    {

        $user = $userRepository->find($this->getUser());

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

            return $this->redirectToRoute('user');
        }

        return $this->render('user/modifier.html.twig', [
            'userForm' => $userForm->createView(),
        ]);
    }
}
