<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuFormType;
use App\Repository\LieuRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    /**
     *
     */
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {
        $lieu = new Lieu();

        $lieuForm = $this->createForm(LieuFormType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isValid() && $lieuForm->isSubmitted())
        {
            $entityManager->persist($lieu);
            $entityManager->flush();

            return $lieu; //TODO
        }

        return $this->render('lieu/index.html.twig', [
            'LieuForm' => $lieuForm->createView(),
        ]);
    }

    /**
     * @Route("/", name="lieu_get")
     * ça ne fonctionne pas, je n'arrive pas à appeler cette méthode
     */
    public function detail(EntityManagerInterface $entityManager,
                           Request $request,
                           LieuRepository $lieuRepository):Response
    {
        $idLieu = $request->get('id');
        if ($idLieu)
        {
            $lieu = $lieuRepository->findBy($idLieu);
            return new JsonResponse($lieu);
        }
        return new Response("ce lieu n'existe pas");
    }
}
