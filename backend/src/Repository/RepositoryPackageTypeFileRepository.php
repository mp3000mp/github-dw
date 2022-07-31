<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RepositoryPackageTypeFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @method RepositoryPackageTypeFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepositoryPackageTypeFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepositoryPackageTypeFile[]    findAll()
 * @method RepositoryPackageTypeFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<RepositoryPackageTypeFile>
 */
class RepositoryPackageTypeFileRepository extends ServiceEntityRepository
{
    /**
     * RepositoryPackageTypeFileRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepositoryPackageTypeFile::class);
    }

    #[ArrayShape([
        'error' => 'string',
        'date' => \DateTime::class,
        'path' => 'string',
        'url' => 'string',
    ])]
    public function findErrors(\DateTime $from): array
    {
        return $this->createQueryBuilder('rptf')
            ->select(['rptf.routineError as error', 'rptf.routine3At as date', 'rptf.path', 'r.url'])
            ->innerJoin('rptf.repository', 'r')
            ->where('rptf.routineError IS NOT NULL')
            ->andWhere('rptf.routineError >= :from')
            ->setParameter('from', $from)
            ->orderBy('rptf.routine3At', 'desc')
            ->getQuery()
            ->getArrayResult();
    }

    public function stats(): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('routine3Count', 'routine3Count', 'integer');
        $rsm->addScalarResult('routine3DoneCount', 'routine3DoneCount', 'integer');
        $rsm->addScalarResult('routine3ErrorCount', 'routine3ErrorCount', 'integer');
        $sql = "SELECT count(1) routine3Count, sum(routine3_at IS NOT NULL) routine3DoneCount, sum(routine_error IS NOT NULL) routine3ErrorCount
            FROM dw_repository_package_type_file
        ";

        return $this->getEntityManager()->createNativeQuery($sql, $rsm)
            ->getScalarResult()[0];
    }
}
