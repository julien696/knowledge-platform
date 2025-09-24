<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Theme>
 */
class ThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    /**
     * Récupère un thème avec tous ses cursus et leurs leçons
     *
     * @param int $themeId
     * @return Theme|null
     */
    public function findWithCursusAndLessons(int $themeId): ?Theme
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.cursus', 'c')
            ->addSelect('c')
            ->leftJoin('c.lessons', 'l')
            ->addSelect('l')
            ->where('t.id = :themeId')
            ->setParameter('themeId', $themeId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
