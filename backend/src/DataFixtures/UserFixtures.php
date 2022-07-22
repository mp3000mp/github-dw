<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements ContainerAwareInterface
{
    private ?UserPasswordHasherInterface $hasher;
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager): void
    {
        $this->hasher = $this->container->get('security.user_password_hasher');
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
