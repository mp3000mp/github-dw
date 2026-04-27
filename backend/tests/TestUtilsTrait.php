<?php

namespace App\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

trait TestUtilsTrait
{
    protected EntityManagerInterface $em;

    protected function purgeDatabase(): void
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        \assert($em instanceof EntityManagerInterface);
        $this->em = $em;

        // reset database
        $purger = new ORMPurger($this->em, []);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $loader = self::getContainer()->get('doctrine.fixtures.loader');
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute($loader->getFixtures());
    }

    protected function terminateTest(): void
    {
        $this->em->close();
        unset($this->em);
    }
}
