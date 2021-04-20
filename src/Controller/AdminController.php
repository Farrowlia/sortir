<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AdminController extends AbstractController
{
    /**
     * @Route("admin/users", name="gestionUsers")
     */
    public function gestionUsers(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response
    {
        //Affichage de la liste des users
        $tableauUsers = $userRepository->findAll();

        //Si requête ajax 'rechUser' reçue, affichage de la recherche
        if ($request->get('rechUser')) {

            $motRecherche = $request->get('rechUser');
//            $result = $userRepository->findByMotRecherche((string)array("nom" => $motRecherche));

            $result = $userRepository->findBy(array("nom" => $motRecherche), array("nom" => "ASC"), null, 0);

            return new JsonResponse([
                'content' => $this->renderView('admin/content/_userSearch.html.twig', compact('result'))
            ]);
        }

        return $this->render('admin/gererLesUsers.html.twig', [
            'users' => $tableauUsers
        ]);
    }
//
//    public function findByMotRecherche(string $motRecherche) {
//
//        $queryBuilder = $this->createQueryBuilder('m');
//        $queryBuilder->add('select', new Expr\Select(array('u')))
//                        ->add('from', new Expr\From('User', 'u'))
//                        ->add('where', $queryBuilder->expr()->orX(
//                            $queryBuilder->expr()->eq('u.nom', '?1'),
//                            $queryBuilder->expr()->like('u.prenom', '?2'),
//                            $queryBuilder->expr()->like('u.pseudo', '?3'),
//                            $queryBuilder->expr()->like('u.email', '?4')
//                        ));
//        $query = $queryBuilder->getQuery();
//        return $query->getResult();
//
//    }

//    public function findByMotRecherche(string $motRecherche): ?User {
//
//        dd('test');
//        $queryBuilder = $this->createQueryBuilder('u');
//        $queryBuilder->add('select', 'u')
//            ->add('from', 'User')
//            ->add('where', $queryBuilder->expr()->orX(
//                $queryBuilder->expr()->eq('u.nom', '?1'),
//                $queryBuilder->expr()->like('u.prenom', '?2'),
//                $queryBuilder->expr()->like('u.pseudo', '?3'),
//                $queryBuilder->expr()->like('u.email', '?4')
//            ));
//        $query = $queryBuilder->getQuery();
//        return $query->getResult();
//
//    }
}
