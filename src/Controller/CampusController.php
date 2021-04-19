<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusFormType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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

            $entityManager->persist($campus);
            $entityManager->flush();

            $this->addFlash('success', 'Le campus a été ajouté !');
            return $this->redirectToRoute('campus');

            //TODO voir l'ajout d'une popup de confirmation ou message flash
        }

        if ($request->get('ajax')) {

            $campusUpdate = $campusRepository->find($request->get('id'));
            $campusUpdate->setNom($request->get("nom"));
            $entityManager->flush();

            return new JsonResponse([
                'content' => $this->renderView('admin/content/_campus.html.twig', compact('campusUpdate'))

            ]);
        }

        return $this->render('admin/gererLesCampus.html.twig', [
            'campusForm' => $campusForm->createView(),
            'tableauCampus' => $tableauCampus
        ]);
    }

    /**
     * @Route("/admin/campus/delete/{id}", name="campus_delete")
     */
    public function delete(int $id, CampusRepository $campusRepository): Response {

        $entityManager = $this->getDoctrine()->getManager();
        $campus = $campusRepository->find($id);
        $entityManager->remove($campus);
        $entityManager->flush();

        $this->addFlash('message', 'Campus supprimé avec succès');
        return $this->redirectToRoute('campus');

    }
}
