<?php

namespace App\Repository;

use App\Entity\Campus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Campus|null find($id, $lockMode = null, $lockVersion = null)
 * @method Campus|null findOneBy(array $criteria, array $orderBy = null)
 * @method Campus[]    findAll()
 * @method Campus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campus::class);
    }

    /**
     * @return Campus|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneCampus(): ?Campus
    {
    //TODO vérifier le nom du champs de saisie

        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder ->andWhere('c.exampleField = :val');
        $query = $queryBuilder->getQuery();

        return $query -> getResult();
    }

}
