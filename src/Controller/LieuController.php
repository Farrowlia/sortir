<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
