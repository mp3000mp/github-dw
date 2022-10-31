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
        $q = $this->getEntityManager()->createQuery('
            SELECT partial p.{id,name} FROM App\Entity\Package p
            JOIN p.packageTypeFile ptf
            WHERE ptf.language = :language
            AND p.name LIKE :text_like
            ORDER BY case when p.name like :text_like_prefix then 2 else case when p.name like :text_like then 1 else 0 end end desc
        ')
            ->setMaxResults(100)
            ->setParameter('language', $language)
            ->setParameter('text_like_prefix', "$text%")
            ->setParameter('text_like', "%$text%");

        // todo score always 0 because not natural language
//        $q = $this->getEntityManager()->createQuery('
//            SELECT partial p.{id,name} FROM App\Entity\Package p
//            JOIN p.packageTypeFile ptf
//            WHERE ptf.language = :language
//            AND match_against(p.name, :text_like) > 0
//            ORDER BY match_against(p.name, :text_like) DESC
//        ')
//            ->setMaxResults(100)
//            ->setParameter('language', $language)
//            ->setParameter('text_like', $text);

        return $q->getResult();
    }
}
