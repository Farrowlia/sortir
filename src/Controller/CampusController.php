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
        //Affichage de la liste des campus
        $tableauCampus = $campusRepository->findAll();

        //Affichage du formulaire pour la création d'un campus
        $campus = new Campus();
        $campusForm = $this->createForm(CampusFormType::class, $campus);
        $campusForm = $campusForm->handleRequest($request);

        //Si le formulaire est soumis et valide, le nouveau campus est ajouté en BDD
        if ($campusForm->isSubmitted() && $campusForm->isValid()) {

            $entityManager->persist($campus);
            $entityManager->flush();

            $this->addFlash('success', 'Le campus a été ajouté !');
            return $this->redirectToRoute('campus');
        }

        //Si requête ajax 'nom' reçue, modification d'un campus
        if ($request->get('nom')) {

            $campusUpdate = $campusRepository->find($request->get('id'));
            $campusUpdate->setNom($request->get("nom"));
            $entityManager->flush();

            return new JsonResponse([
                'content' => $this->renderView('admin/content/_campus.html.twig', compact('campusUpdate'))
            ]);
        }

        //Si requête 'rechCampus' reçue, affichage de la recherche
        if ($request->get('rechCampus')) {

            $motRecherche = $request->get('rechCampus');
            $result = $campusRepository->findBy(array("nom" => $motRecherche), array("nom" => "ASC"), null, 0);

            return new JsonResponse ([
                'content' => $this->renderView('admin/content/_campusSearch.html.twig', compact('result'))
            ]);
        }

        return $this->render('admin/gererLesCampus.html.twig', [
            'campusForm' => $campusForm->createView(),
            'tableauCampus' => $tableauCampus
        ]);
    }

    /**
     * Supression d'un campus en BDD
     * @Route("/admin/campus/delete/{id}", name="campus_delete")
     */
    public function delete(int $id, CampusRepository $campusRepository): Response {

        $entityManager = $this->getDoctrine()->getManager();
        $campus = $campusRepository->find($id);
        $entityManager->remove($campus);
        $entityManager->flush();

        $this->addFlash('success', 'Campus supprimé avec succès');
        return $this->redirectToRoute('campus');

    }
}
