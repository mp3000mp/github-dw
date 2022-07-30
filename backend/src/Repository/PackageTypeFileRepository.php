<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PackageTypeFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @method PackageTypeFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method PackageTypeFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method PackageTypeFile[]    findAll()
 * @method PackageTypeFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<PackageTypeFile>
 */
class PackageTypeFileRepository extends ServiceEntityRepository
{
    /**
     * PackageTypeFileRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PackageTypeFile::class);
    }

    #[ArrayShape([
        'language' => 'string',
        'count' => 'int',
    ])]
    public function stats(): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('nb', 'count', 'integer');
        $sql = "SELECT dw_package_type_file.id, COUNT(1) AS nb 
                    FROM dw_package_type_file 
                    LEFT JOIN dw_package ON dw_package_type_file.id = dw_package.package_type_file_id
                    GROUP BY dw_package_type_file.id
        ";

        return $this->getEntityManager()->createNativeQuery($sql, $rsm)
            ->getScalarResult();
    }
}
