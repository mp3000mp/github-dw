<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Repository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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
    public function findErrors(\DateTime $from, int $limit = 100): array
    {
        return $this->createQueryBuilder('r')
            ->select(['r.routineError as error', 'r.routine2At as date', 'r.url'])
            ->where('r.routineError IS NOT NULL')
            ->andWhere('r.routineError >= :from')
            ->setParameter('from', $from)
            ->orderBy('r.routine2At', 'desc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return Repository[]
     */
    public function findWithQuery(\stdClass $query, int $offset, int $limit): array
    {
        $q = $this->buildQuery($query, false);
        $q->setParameter('offset', $offset);
        $q->setParameter('limit', $limit);

        return $q->getResult();
    }

    public function countWithQuery(\stdClass $query): int
    {
        $q = $this->buildQuery($query, true);

        $r = $q->getScalarResult();

        return (int) $r[0]['nb'];
    }

    private function buildQuery(\stdClass $query, bool $isCounting): NativeQuery
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());

        $sqlSelect = 'SELECT r.id, r.full_name, r.name, r.url, r.license_name, r.description, r.forks_count, r.open_issues_count, r.stargazers_count, r.created_at, r.pushed_at';
        $sqlFrom = 'FROM dw_repository r';
        $sqlJoins = [];
        $sqlWhere = ' WHERE r.routine2_at IS NOT NULL AND r.routine_error IS NULL ';
        $sqlGroupBy = '';
        $sqlOrderBy = '';
        $sqlLimit = '';
        $params = [];

        if ($isCounting) {
            $rsm->addScalarResult('nb', 'nb', 'integer');
            $sqlSelect = 'SELECT COUNT(DISTINCT r.id) AS nb';
        } else {
            $rsm->addRootEntityFromClassMetadata(Repository::class, 'r');
            $sqlGroupBy = 'GROUP BY r.id';
            $sqlOrderBy = 'ORDER BY '.implode(', ', ['r.stargazers_count DESC']);
            $sqlLimit = 'LIMIT :limit OFFSET :offset';
        }

        if (null !== $query->name) {
            $sqlWhere .= ' AND r.name LIKE :name ';
            $params['name'] = '%'.$query->name.'%';
        }
        // todo: fulltext
        if (null !== $query->description) {
            $sqlWhere .= ' AND r.description LIKE :description ';
            $params['description'] = '%'.$query->description.'%';
        }
        // todo: postgres with ?
        if (count($query->packages) > 0) {
            $i = 1;
            foreach ($query->packages as $package) {
                $join = "INNER JOIN dw_repository_package rp$i ON r.id = rp$i.repository_id AND rp$i.package_id = :package_id$i ";
                $params["package_id$i"] = $package->id;

                if (null !== $package->minVersion) {
                    // max_repo > min_search
                    $version = explode('.', $package->minVersion);
                    $join .= " AND (
                        rp$i.version_max_major > :package_min_major$i 
                        OR (
                            rp$i.version_max_major = :package_min_major$i
                            AND (
                                rp$i.version_max_minor > :package_min_minor$i 
                                OR (
                                    rp$i.version_max_minor = :package_min_minor$i
                                    AND rp$i.version_max_patch > :package_min_patch$i
                                )                                
                            )
                        )
                    ) ";
                    $params["package_min_major$i"] = $version[0];
                    $params["package_min_minor$i"] = $version[1];
                    $params["package_min_patch$i"] = $version[2];
                }
                if (null !== $package->maxVersion) {
                    // min_repo < max_search
                    $version = explode('.', $package->maxVersion);
                    $join .= " AND (
                        rp$i.version_min_major < :package_max_major$i 
                        OR (
                            rp$i.version_min_major = :package_max_major$i
                            AND (
                                rp$i.version_min_minor < :package_max_minor$i 
                                OR (
                                    rp$i.version_min_minor = :package_max_minor$i
                                    AND rp$i.version_min_patch < :package_max_patch$i
                                )                                
                            )
                        )
                    ) ";
                    $params["package_max_major$i"] = $version[0];
                    $params["package_max_minor$i"] = $version[1];
                    $params["package_max_patch$i"] = $version[2];
                }

                $sqlJoins[] = $join;
                ++$i;
            }
        }
        $sql = implode(' ', [$sqlSelect, $sqlFrom, implode(' ', $sqlJoins), $sqlWhere, $sqlGroupBy, $sqlOrderBy, $sqlLimit]);

        return $this->getEntityManager()->createNativeQuery($sql, $rsm)
            ->setParameters($params);
    }

    public function stats(): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('routine2Count', 'routine2Count', 'integer');
        $rsm->addScalarResult('routine2DoneCount', 'routine2DoneCount', 'integer');
        $rsm->addScalarResult('routine2ErrorCount', 'routine2ErrorCount', 'integer');
        $sql = '
            SELECT count(1) routine2Count, sum(routine2_at IS NOT NULL) routine2DoneCount, sum(routine_error IS NOT NULL) routine2ErrorCount
            FROM dw_repository
        ';

        return $this->getEntityManager()->createNativeQuery($sql, $rsm)
            ->getScalarResult()[0];
    }

    public function timelineRoutine1(\DateTime $minDate): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('label', 'label', 'string');
        $rsm->addScalarResult('done', 'done', 'integer');
        $sql = "
            SELECT DATE_FORMAT(routine1_at, '%Y-%m-%d') label, count(1) done
            FROM dw_repository
            WHERE routine1_at > :min_date
            GROUP BY DATE_FORMAT(routine1_at, '%Y-%m-%d')
        ";

        return $this->getEntityManager()->createNativeQuery($sql, $rsm)
            ->setParameter('min_date', $minDate)
            ->getScalarResult();
    }

    public function timelineRoutine2(\DateTime $minDate): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('label', 'label', 'string');
        $rsm->addScalarResult('done', 'done', 'integer');
        $rsm->addScalarResult('errors', 'errors', 'integer');
        $sql = "
            SELECT DATE_FORMAT(routine2_at, '%Y-%m-%d') label, count(1) done, sum(routine_error IS NOT NULL) errors
            FROM dw_repository
            WHERE routine2_at > :min_date
            GROUP BY DATE_FORMAT(routine2_at, '%Y-%m-%d')
        ";

        return $this->getEntityManager()->createNativeQuery($sql, $rsm)
            ->setParameter('min_date', $minDate)
            ->getScalarResult();
    }
}
