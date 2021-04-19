<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleFormType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
    /**
     * @Route("admin/villes", name="villes")
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        VilleRepository $villeRepository
    ): Response
    {
        $tableauVilles = $villeRepository->findAll();

        $ville = new Ville();
        $villeForm = $this->createForm(VilleFormType::class, $ville);
        $villeForm = $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {

            $entityManager->persist($ville);
            $entityManager->flush();

            $this->addFlash('success', 'La ville a été ajoutée !');
            return $this->redirectToRoute('villes');

            //TODO voir l'ajout d'une popup de confirmation ou message flash
        }

        if ($request->get('codePostal')) {

            $villeUpdate = $villeRepository->find($request->get('id'));
            $villeUpdate->setNom($request->get("nom"));
            $villeUpdate->setCodePostal($request->get("codePostal"));
            $entityManager->flush();

            return new JsonResponse([
                'content' => $this->renderView('admin/content/_villes.html.twig', compact('villeUpdate'))
            ]);
        }

        if ($request->get('rechVille')) {

            $motRecherche = $request->get('rechVille');
            $result = $villeRepository->findBy(array("nom" => $motRecherche), array("nom" => "ASC"), null, 0);

            return new JsonResponse([
                'content' => $this->renderView('admin/content/_villeSearch.html.twig', compact('result'))
            ]);
        }

        return $this->render('admin/gererLesVilles.html.twig', [
            'villeForm' => $villeForm->createView(),
            'villes' => $tableauVilles
        ]);
    }

    /**
     * @Route("/admin/villes/delete/{id}", name="ville_delete")
     */
    public function delete(int $id, VilleRepository $villeRepository): Response
    {

        $entityManager = $this->getDoctrine()->getManager();
        $ville = $villeRepository->find($id);
        $entityManager->remove($ville);
        $entityManager->flush();

        $this->addFlash('message', 'Ville supprimée avec succès');
        return $this->redirectToRoute('villes');

    }

}
