<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $password = 'Test2000!';

        // admin
        $user = new User();
        $hashedPassword = $this->hasher->hashPassword($user, $password);
        $user->setEmail('admin@mp3000.fr');
        $user->setUsername('admin');
        $user->setPassword($hashedPassword);
        $user->setPasswordUpdatedAt(new \DateTime());
        $user->setRoles(['ROLE_ADMIN']);
        $user->setIsEnabled(true);

        $manager->persist($user);
        $this->addReference(AppFixtures::USER_ADMIN, $user);

        $manager->flush();
    }
}
