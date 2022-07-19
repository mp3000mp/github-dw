<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PackageTypeFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
}
