<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;

trait TestUtilsTrait
{
    protected EntityManagerInterface $em;

    protected function purgeDatabase(): void
    {
        // utils
        $this->em = self::getContainer()
            ->get('doctrine')
            ->getManager();

        // reset database
        $purger = new ORMPurger($this->em, []);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $loader = new ContainerAwareLoader(self::getContainer());
        $loader->addFixture(new AppFixtures());
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute($loader->getFixtures());
    }

    protected function terminateTest(): void
    {
        $this->em->close();
        unset($this->em);
    }
}
