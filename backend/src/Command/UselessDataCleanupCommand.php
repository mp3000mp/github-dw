<?php

namespace App\Command;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:data:cleanup', description: 'Clean up useless data.')]
class UselessDataCleanupCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command clean up useless data (and compute package usage).');
        $this->addOption('force-flush', 'f', InputOption::VALUE_NONE, 'Force flush');
        $this->addOption('min-packages', '-m', InputOption::VALUE_REQUIRED, 'Minimum packages usage to be kept', 2);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Computing useless data...');

        $dryRun = !$input->getOption('force-flush');
        $minPackages = (int) $input->getOption('min-packages');
        $rows = [];

        if ($dryRun) {
            $io->info('DRY RUN MODE - MIN='.$minPackages);
        } else {
            $io->warning('FLUSH MODE - MIN='.$minPackages);
        }
        $io->createProgressBar(4 + (int) !$dryRun);
        $io->progressStart();
        if (!$dryRun) {
            $this->em->getConnection()->beginTransaction();
        }

        try {
            // usage
            $io->progressAdvance();

            $sql = '
                UPDATE dw_package p
                INNER JOIN (
                  SELECT package_id, COUNT(1) AS pCount
                  FROM dw_repository_package
                  GROUP BY package_id
                ) j
                ON j.package_id = p.id
                SET p.nb = j.pCount
            ';
            $this->em->getConnection()->executeQuery($sql);

            // packages
            $io->progressAdvance();

            $sqlTotal = 'SELECT COUNT(1) total FROM dw_package';
            $sql = "
            SELECT id 
            FROM dw_package 
            WHERE dw_package.nb < $minPackages        
        ";
            $ids = $this->getIds($sql);
            $rows[] = $this->getRow('Packages', count($ids), $sqlTotal);
            if (!$dryRun && count($ids) > 0) {
                $this->doDelete('DELETE FROM dw_repository_package WHERE package_id IN (:ids)', $ids);
                $this->doDelete('DELETE FROM dw_package WHERE id IN (:ids)', $ids);
            }

            // repo package type files
            $io->progressAdvance();

            $sqlTotal = 'SELECT COUNT(1) total FROM dw_repository_package_type_file';
            $sql = '
            SELECT dw_repository_package_type_file.id, COUNT(1) 
            FROM dw_repository_package_type_file
            LEFT JOIN dw_repository_package ON dw_repository_package_type_file.id = dw_repository_package.repository_package_type_file_id '.
                ($dryRun && count($ids) > 0 ? 'AND dw_repository_package.package_id NOT IN (:ids)' : '')
                ." GROUP BY dw_repository_package_type_file.id 
            HAVING COUNT(1) < $minPackages        
        ";
            $ids = $this->getIds($sql, $ids);
            $rows[] = $this->getRow('Repo package type files', count($ids), $sqlTotal);
            if (!$dryRun && count($ids) > 0) {
                $this->doDelete('DELETE FROM dw_repository_package WHERE repository_package_type_file_id IN (:ids)', $ids);
                $this->doDelete('DELETE FROM dw_repository_package_type_file WHERE id IN (:ids)', $ids);
            }

            // repositories
            $io->progressAdvance();

            $sqlTotal = 'SELECT COUNT(1) total FROM dw_repository';
            $sql = '
            SELECT dw_repository.id, COUNT(1) 
            FROM dw_repository
            LEFT JOIN dw_repository_package ON dw_repository.id = dw_repository_package.repository_id '.
                ($dryRun && count($ids) ? 'AND repository_package_type_file_id NOT IN (:ids)' : '')
                ." GROUP BY dw_repository.id 
            HAVING COUNT(1) < $minPackages        
        ";
            $ids = $this->getIds($sql, $ids);
            $rows[] = $this->getRow('Repositories', count($ids), $sqlTotal);
            if (!$dryRun && count($ids) > 0) {
                $this->doDelete('DELETE FROM dw_repository_package WHERE repository_id IN (:ids)', $ids);
                $this->doDelete('DELETE FROM dw_repository_language WHERE repository_id IN (:ids)', $ids);
                $this->doDelete('DELETE FROM dw_repository_topic WHERE repository_id IN (:ids)', $ids);
                $this->doDelete('DELETE FROM dw_repository_package_type_file WHERE repository_id IN (:ids)', $ids);
                $this->doDelete('DELETE FROM dw_repository WHERE id IN (:ids)', $ids);
            }
        } catch (\Exception $e) {
            if (!$dryRun) {
                $this->em->getConnection()->rollBack();
            }
            $output->writeln('FAILED: '.$e->getMessage());

            return Command::FAILURE;
        }
        if (!$dryRun) {
            $io->progressAdvance();
            $this->em->getConnection()->commit();
        }

        $io->progressFinish();
        $io->success('SUCCESS');
        $io->table(['Step', 'Useless', 'Total', 'Ratio'], $rows);

        return Command::SUCCESS;
    }

    /**
     * @param int[]|null $ids
     *
     * @return int[]
     */
    private function getIds(string $sql, ?array $ids = null): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $q = $this->em->createNativeQuery($sql, $rsm);
        if (null !== $ids) {
            $q->setParameter('ids', $ids);
        }

        return $q->getSingleColumnResult();
    }

    private function getTotal(string $sql): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total', 'total', 'integer');
        $q = $this->em->createNativeQuery($sql, $rsm);
        $r = $q->getSingleResult();

        return $r['total'];
    }

    /**
     * @return string[]
     */
    private function getRow(string $label, int $ids, string $sqlTotal): array
    {
        $total = $this->getTotal($sqlTotal);

        return [
            $label,
            (string) $ids,
            (string) $total,
            0 === $total ? 'NA' : ((string) (round($ids / $total, 2) * 100)).'%',
        ];
    }

    /**
     * @param int[] $ids
     */
    public function doDelete(string $sql, array $ids): void
    {
        $this->em->getConnection()->executeQuery($sql, ['ids' => $ids], ['ids' => ArrayParameterType::INTEGER]);
    }
}
