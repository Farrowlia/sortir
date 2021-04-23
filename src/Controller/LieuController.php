<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuFormType;
use App\Repository\CommentaireSortieRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

class LieuController extends AbstractController
{

    /**
     * AJOUTER UN LIEU
     * @Route("/lieu/create", name="create_lieu", methods={"POST"})
     */
    public function createLieu(EntityManagerInterface $entityManager, VilleRepository $villeRepository, Request $request): Response
    {
        $params = json_decode($request->getContent());

        $lieu = new Lieu();
        $lieu->setNom($params->nom);
        $lieu->setRue($params->rue);
        $ville = $villeRepository->find($params->ville);
        $lieu->setVille($ville);
        $lieu->setLatitude((float)$params->latitude);
        $lieu->setLongitude((float)$params->longitude);

        $entityManager->persist($lieu);
        $entityManager->flush();

        return new JsonResponse();

    }

    /**
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
