<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Repository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @method Repository|null find($id, $lockMode = null, $lockVersion = null)
 * @method Repository|null findOneBy(array $criteria, array $orderBy = null)
 * @method Repository[]    findAll()
 * @method Repository[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<Repository>
 */
class RepositoryRepository extends ServiceEntityRepository
{
    /**
     * RepositoryRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repository::class);
    }

    #[ArrayShape([
        'error' => 'string',
        'date' => \DateTime::class,
        'url' => 'string',
    ])]
    public function findErrors(\DateTime $from): array
    {
        return $this->createQueryBuilder('r')
            ->select(['r.routineError as error', 'r.routine2At as date', 'r.url'])
            ->where('r.routineError IS NOT NULL')
            ->andWhere('r.routineError >= :from')
            ->setParameter('from', $from)
            ->orderBy('r.routine2At', 'desc')
            ->getQuery()
            ->getArrayResult();
    }
}
