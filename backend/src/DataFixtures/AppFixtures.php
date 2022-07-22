<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const USER_ADMIN = 'USER_ADMIN';

    public function load(ObjectManager $manager): void
    {
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
