<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommand extends Command
{
    protected EntityManagerInterface $em;
    protected UserPasswordHasherInterface $passwordHasher;
    protected static $defaultName = 'app:user:create';

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this->setDescription('Create new user.');
        $this->setHelp('This command creates a new user.');
        $this->addArgument('username', InputArgument::REQUIRED, 'Username');
        $this->addArgument('email', InputArgument::REQUIRED, 'Email');
        $this->addArgument('password', InputArgument::REQUIRED, 'Password');
        $this->addOption('is-admin', 'a', InputOption::VALUE_NONE, 'Is admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(['Creating user '.$input->getArgument('username')]);

        $roles = ['ROLE_USER'];
        if ($input->getOption('is-admin')) {
            $roles[] = 'ROLE_ADMIN';
        }

        $user = new User();
        $user->setIsEnabled(true);
        $user->setUsername($input->getArgument('username'));
        $user->setEmail($input->getArgument('email'));
        $user->setPasswordUpdatedAt(new \DateTime());
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $input->getArgument('password')));

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln(['SUCCESS (id='.$user->getId().')']);

        return Command::SUCCESS;
    }
}
