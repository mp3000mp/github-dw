<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Package;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @method Package|null find($id, $lockMode = null, $lockVersion = null)
 * @method Package|null findOneBy(array $criteria, array $orderBy = null)
 * @method Package[]    findAll()
 * @method Package[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<Package>
 */
class PackageRepository extends ServiceEntityRepository
{
    /**
     * PackageRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Package::class);
    }

    #[ArrayShape([
        'id' => 'int',
        'name' => 'string',
    ])]
    public function autocomplete(string $language, string $text): array
    {
        // todo use levenstein ?
        return $this->createQueryBuilder('p')
            ->select(['p.id', 'p.name'])
            ->innerJoin('p.packageTypeFile', 'ptf')
            ->where('ptf.language = :language')
            ->andWhere('p.name like :text_like')
            ->setParameter('language', $language)
            ->setParameter('text_like', "$text%")
            ->getQuery()
            ->getArrayResult();
    }
}
