<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RepositoryPackage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @method RepositoryPackage|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepositoryPackage|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepositoryPackage[]    findAll()
 * @method RepositoryPackage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<RepositoryPackage>
 */
class RepositoryPackageRepository extends ServiceEntityRepository
{
    /**
     * RepositoryPackageRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepositoryPackage::class);
    }

    #[ArrayShape([
        'id' => 'int',
        'name' => 'string',
    ])]
    public function autocomplete(string $language, string $text): array
    {
        // todo use levenstein ?
        return $this->createQueryBuilder('rp')
            ->select(['rp.id', 'rp.name'])
            ->innerJoin('rp.repositoryPackageTypeFile', 'rptf')
            ->innerJoin('rptf.packageTypeFile', 'ptf')
            ->where('ptf.language = :language')
            ->andWhere('rp.name like :text_like')
            ->setParameter('language', $language)
            ->setParameter('text_like', "$text%")
            ->getQuery()
            ->getArrayResult();
    }
}
