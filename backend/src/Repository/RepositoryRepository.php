<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Media;
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
        return (int) ($r['nb'] ?? 0);

    }

    private function buildQuery(\stdClass $query, bool $isCounting): NativeQuery
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());

        $sqlSelect = 'SELECT r.id, r.name, r.url, r.description, r.forks_count, r.open_issues_count, r.stargazers_count, r.created_at, r.pushed_at';
        $sqlFrom = 'FROM dw_repository r';
        $sqlJoins = [];
        $sqlWhere = ' WHERE r.routine2_at IS NOT NULL AND r.routine_error IS NULL ';
        $sqlGroupBy = 'GROUP BY r.id';
        $sqlOrderBy = '';
        $sqlLimit = '';
        $params = [];

        if ($isCounting) {
            $rsm->addScalarResult('nb', 'nb', 'integer');
            $sqlSelect = 'SELECT COUNT(DISTINCT r.id) AS nb';
        } else {
            $rsm->addRootEntityFromClassMetadata(Repository::class, 'r');
            $sqlOrderBy = 'ORDER BY '.implode(', ', ['r.stargazers_count DESC']);
            $sqlLimit = 'LIMIT :limit OFFSET :offset';
        }

        if (null !== $query->name) {
            $sqlWhere .= ' AND name LIKE :name ';
            $params['name'] = '%' . $query->name . '%';
        }
        // todo: fulltext
        if (null !== $query->description) {
            $sqlWhere .= ' AND name LIKE :description ';
            $params['description'] = '%' . $query->description . '%';
        }
        // todo: postgres with ?
        if (count($query->packages) > 0) {
            $i = 1;
            foreach ($query->packages as $package) {
                $sqlJoins = ["INNER JOIN dw_repository_package rp$i ON r.id = rp.repository_id"];
                $sqlWhere .= " AND rp.package_id = :package_id$i ";
                $params["package_id$i"] = $package->id;
                if (null !== $package->minVersion) {
                    $version = explode('.', $package->minVersion);
                    $sqlWhere .= " AND rp.package_id >= :package_min_major$i ";
                    $params["package_min_major$i"] = $version[0];
                    $sqlWhere .= " AND rp.package_id >= :package_min_minor$i ";
                    $params["package_min_minor$i"] = $version[1];
                    $sqlWhere .= " AND rp.package_id >= :package_min_patch$i ";
                    $params["package_min_patch$i"] = $version[2];
                }
                if (null !== $package->maxVersion) {
                    $version = explode('.', $package->maxVersion);
                    $sqlWhere .= " AND rp.package_id <= :package_max_major$i ";
                    $params["package_max_major$i"] = $version[0];
                    $sqlWhere .= " AND rp.package_id <= :package_max_minor$i ";
                    $params["package_max_minor$i"] = $version[1];
                    $sqlWhere .= " AND rp.package_id <= :package_max_patch$i ";
                    $params["package_max_patch$i"] = $version[2];
                }
                $i++;
            }
        }

        $sql = implode(' ', [$sqlSelect, $sqlFrom, implode(' ', $sqlJoins), $sqlWhere, $sqlGroupBy, $sqlOrderBy, $sqlLimit]);

        echo $sql;

        return $this->getEntityManager()->createNativeQuery($sql, $rsm)
            ->setParameters($params);
    }
}
