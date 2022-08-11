<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_ADMIN = 'USER_ADMIN';

    public function load(ObjectManager $manager): void
    {
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            DwFixtures::class,
        ];
    }
}
