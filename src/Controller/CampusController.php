<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusFormType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CampusController extends AbstractController
{
    /**
     * @Route("admin/campus", name="campus")
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        CampusRepository $campusRepository
    ): Response
    {
        $tableauCampus = $campusRepository->findAll();

        $campus = new Campus();
        $campusForm = $this->createForm(CampusFormType::class, $campus);
        $campusForm = $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $campus->setNom();

            $entityManager->persist();
            $entityManager->flush();

            //TODO voir l'ajout d'une popup de confirmation ou message flash
            //return vers un rafraichissement de la page ?
        }

        return $this->render('admin/gererLesCampus.html.twig', [
            'campusForm' => $campusForm->createView(),
            'tableauCampus' => $tableauCampus
        ]);
    }
}
