<?php

namespace App\Tests\Doctrine;

use App\Entity\Package;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MatchAgainstTest extends KernelTestCase
{
    public function testGetSql(): void
    {
        self::bootKernel();
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $query = $em->createQuery('
            SELECT p FROM '.Package::class.' p
            WHERE match_against(p.name, :text) > 0
        ')->setParameter('text', 'foo');

        $sql = $query->getSQL();

        self::assertStringContainsString('match(', $sql);
        self::assertStringContainsString(') against(', $sql);
    }
}
