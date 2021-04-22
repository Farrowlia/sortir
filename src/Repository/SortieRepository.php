<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\User;
use App\Services\SearchSortie;
use App\Services\SearchSortieUser;
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

    public function findSearchUser(SearchSortieUser $searchSortieUser, User $user): PaginationInterface
    {
        $query = $this->getSearchQueryUser($searchSortieUser, $user)->getQuery();
        return $this->paginator->paginate($query, $searchSortieUser->page, 12);
    }

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
                ->andWhere('s.etat = 5')
                ->andWhere('s.dateDebut < :today')
                ->andWhere('s.dateDebut > :filtre1MonthArchive')
                ->setParameter('today', new \DateTime())
                ->setParameter('filtre1MonthArchive', date_modify(new \DateTime(), '-1 month'));
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

    private function getSearchQueryUser(SearchSortieUser $searchSortieUser, User $user): QueryBuilder
    {
        $query = $this
            ->createQueryBuilder('s')
            ->select('c', 's', 'o', 'u')
            ->join('s.participants', 'u')
            ->join('s.organisateur', 'o')
            ->join('s.etat', 'e')
            ->join('s.campus', 'c');

        if (!empty($searchSortieUser->sortieQueJorganise)) {
            $query = $query
                ->orWhere('o = :idUser')
                ->setParameter('idUser', $user->getId());
        }

        if (!empty($searchSortieUser->sortieAuquelJeParticipe)) {
            $query = $query
                ->orWhere('u = :idUser')
                ->setParameter('idUser', $user->getId());
        }

        if (empty($searchSortieUser->archive)) {
            $query = $query
                ->andWhere('s.etat = 2');
        }

        if (!empty($searchSortieUser->archive)) {
            $query = $query
                ->andWhere('s.etat = 5')
                ->andWhere('s.dateDebut < :today')
                ->andWhere('s.dateDebut > :filtre1MonthArchive')
                ->setParameter('today', new \DateTime())
                ->setParameter('filtre1MonthArchive', date_modify(new \DateTime(), '-1 month'));
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


}
