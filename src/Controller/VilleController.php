<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleFormType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
    /**
     * @Route("/gererlesvilles", name="gerer_les_villes")
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
            $ville->setNom();

            $entityManager->persist();
            $entityManager->flush();

            //TODO voir l'ajout d'une popup de confirmation ou message flash
            //return vers un rafraichissement de la page ?
        }
        return $this->render('Ville/gererLesVilles.html.twig', [
            'villeForm' => $villeForm->createView(),
            'villes' => $tableauVilles
        ]);
    }
}
