<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:health-check', description: 'Check external services.')]
class HealthCheckCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command checks services are available.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking database...');

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('s', 's', 'integer');
        $q = $this->em->createNativeQuery('SELECT 1+1 AS s', $rsm);

        $r = $q->getSingleScalarResult();
        if (2 !== $r) {
            $output->writeln('FAILED');

            return Command::FAILURE;
        }

        $output->writeln('SUCCESS');

        return Command::SUCCESS;
    }
}
