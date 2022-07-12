<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HealthCheckCommand extends Command
{
    protected static $defaultName = 'app:health-check';
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this->setDescription('Health check.');
        $this->setHelp('This command checks services are available.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking database...');

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('s', 's', 'integer');
        $q = $this->em->createNativeQuery('SELECT 1+1 AS s', $rsm);

        $r = $q->getSingleScalarResult();
        if ($r !== 2) {
            $output->writeln('FAILED');

            return Command::FAILURE;
        }

        // todo check mailer

        $output->writeln('SUCCESS');

        return Command::SUCCESS;
    }
}
