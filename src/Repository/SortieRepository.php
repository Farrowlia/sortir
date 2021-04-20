<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Services\SearchSortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Sortie::class);
        $this->paginator = $paginator;
    }

    public function findSearch(SearchSortie $searchSortie): PaginationInterface
    {
        $query = $this->getSearchQuery($searchSortie)->getQuery();
        return $this->paginator->paginate($query, $searchSortie->page, 12);
    }

//    /**
//     * Récupère le prix minimum et maximum correspondant à une recherche
//     * @return integer[]
//     */
//    public function findMinMax(SearchSortie $searchSortie): array
//    {
//        $results = $this->getSearchQuery($searchSortie)
//            ->select('MIN(p.price) as min', 'MAX(p.price) as max')
//            ->getQuery()
//            ->getScalarResult();
//        return [(int)$results[0]['min'], (int)$results[0]['max']];
//    }

    private function getSearchQuery(SearchSortie $searchSortie): QueryBuilder
    {
        $query = $this
            ->createQueryBuilder('s')
            ->select('c', 's', 'u')
            ->join('s.organisateur', 'u')
            ->join('s.etat', 'e')
            ->join('s.campus', 'c');

        if (!empty($searchSortie->q)) {
            $query = $query
                ->andWhere('s.nom LIKE :q')
                ->orWhere('s.description LIKE :q')
                ->setParameter('q', "%{$searchSortie->q}%");
        }

        if (empty($searchSortie->archive)) {
            $query = $query
                ->andWhere('s.etat = 2');
        }

        if (!empty($searchSortie->archive)) {
            $query = $query
                ->andWhere('s.etat = 5');
        }

        if (!empty($searchSortie->campus)) {
            $query = $query
                ->andWhere('c.id IN (:campus)')
                ->setParameter('campus', $searchSortie->campus);
        }

        if (!empty($searchSortie->dateMin)) {
            $query = $query
                ->andWhere('s.dateDebut > :dateMin')
                ->setParameter('dateMin', $searchSortie->dateMin);
        }

        if (!empty($searchSortie->dateMax)) {
            $query = $query
                ->andWhere('s.dateDebut < :dateMax')
                ->setParameter('dateMax', $searchSortie->dateMax);
        }

        return $query;
    }

    public function etatsUpdate(Etat $etat1, Etat $etat2) {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->update()
            ->andWhere('s.dateCloture > :today')
            ->andWhere('s.etat = :etat')
            ->setParameter('etat', $etat1)
            ->setParameter('today', new \DateTimeImmutable(), Types::DATE_IMMUTABLE)
            ->set('s.etat', ':etat2')
            ->setParameter('etat2', $etat2);
        $queryBuilder->getQuery()->execute();
    }

    public function findByEtat(Etat $etat) {
        $queryBuilder = $this->createQueryBuilder('sortie')
            ->andWhere('sortie.etat = :etat')
            ->andWhere('sortie.dateCloture > :today');

        return $queryBuilder->setParameter('etat', $etat)
            ->setParameter('today', new \DateTimeImmutable(), Types::DATE_IMMUTABLE)
            ->getQuery()
            ->getResult();

    }
//    public function etatsTri() {
//    $this->findBy();
//        return new Paginator($query);
//    }
}
