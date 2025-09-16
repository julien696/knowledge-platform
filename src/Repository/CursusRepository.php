<?php

namespace App\Repository;

use App\Entity\Cursus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cursus>
 */
class CursusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cursus::class);
    }

    /**
     * Récupère les cursus avec leurs relations optimisées
     *
     * @param int $page Le numéro de la page (commence à 1)
     * @param int $limit Le nombre d'éléments par page
     * @return Paginator|Cursus[]
     */
    public function findAllWithRelations(int $page = 1, int $limit = 10)
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.theme', 't')
            ->addSelect('t')
            ->leftJoin('c.lessons', 'l')
            ->addSelect('l')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery();

        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }
}
