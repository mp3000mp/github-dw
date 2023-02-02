<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Package;
use App\Entity\PackageTypeFile;
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
    public function autocomplete(PackageTypeFile $packageTypeFile, string $text): array
    {
        $q = $this->getEntityManager()->createQuery('
            SELECT partial p.{id,name} FROM App\Entity\Package p
            WHERE p.packageTypeFile = :package_file_type
            AND p.name LIKE :text_like
            ORDER BY 
              CASE 
                WHEN p.name = :text THEN 3'.
              //  WHEN p.name LIKE :text_like_prefix THEN 2
              //  WHEN p.name LIKE :text_like THEN 1
                ' ELSE 0
              END DESC,
              p.nb DESC
        ')
            ->setMaxResults(100)
            ->setParameter('package_file_type', $packageTypeFile)
            ->setParameter('text', $text)
            //->setParameter('text_like_prefix', "$text%")
            ->setParameter('text_like', "%$text%");

        // using match_against is not score always 0 because not natural language
        /*
        $q = $this->getEntityManager()->createQuery('
            SELECT partial p.{id,name} FROM App\Entity\Package p
            JOIN p.packageTypeFile ptf
            WHERE ptf.language = :language
            AND match_against(p.name, :text_like) > 0
            ORDER BY match_against(p.name, :text_like) DESC
        ')
            ->setMaxResults(100)
            ->setParameter('language', $language)
            ->setParameter('text_like', $text);
        */

        return $q->getResult();
    }
}
