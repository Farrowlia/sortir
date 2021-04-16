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

        if ($request->get('ajax')){
            dd("test");

            $ville->setNom($request->get("0"));
            $ville->setCodePostal($request->get("1"));

            $entityManager->flush();

            return new JsonResponse([
                'content' => $this->renderView('admin/gererLesVilles..html.twig')
            ]);
        }


        return $this->render('admin/gererLesVilles.html.twig', [
            'villeForm' => $villeForm->createView(),
            'villes' => $tableauVilles
        ]);
    }

}
