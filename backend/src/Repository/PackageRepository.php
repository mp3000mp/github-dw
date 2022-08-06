<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Package;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    /**
     * Caution: DQL partial is used here.
     *
     * @return Package[]
     */
    public function autocomplete(string $language, string $text): array
    {
        // todo use levenstein ?
        $q = $this->getEntityManager()->createQuery('
            SELECT partial p.{id,name} FROM App\Entity\Package p
            JOIN p.packageTypeFile ptf
            WHERE ptf.language = :language
            AND p.name LIKE :text_like
        ')
            ->setParameter('language', $language)
            ->setParameter('text_like', "$text%");

        return $q->getResult();
    }
}
