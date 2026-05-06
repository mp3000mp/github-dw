<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PackageTypeFile;
use App\Entity\Repository;
use App\Entity\RepositoryPackageTypeFile;
use App\Repository\PackageTypeFileRepository;
use App\Repository\RepositoryPackageTypeFileRepository;
use App\Repository\RepositoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    #[Route(path: '/errors', name: 'admin.errors', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function errors(): Response
    {
        /** @var RepositoryRepository $repoRepo */
        $repoRepo = $this->em->getRepository(Repository::class);
        /** @var RepositoryPackageTypeFileRepository $repoPackageRepo */
        $repoPackageRepo = $this->em->getRepository(RepositoryPackageTypeFile::class);

        $d = new \DateTimeImmutable('-7 days');
        $routine2Errors = $repoRepo->findErrors($d, 10);
        $routine3Errors = $repoPackageRepo->findErrors($d, 10);

        return $this->json([
            'routine2' => $routine2Errors,
            'routine3' => $routine3Errors,
        ]);
    }

    #[Route(path: '/stats', name: 'admin.stats', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function stats(): Response
    {
        /** @var RepositoryRepository $repoRepo */
        $repoRepo = $this->em->getRepository(Repository::class);
        /** @var PackageTypeFileRepository $packageTypeFileRepo */
        $packageTypeFileRepo = $this->em->getRepository(PackageTypeFile::class);
        /** @var RepositoryPackageTypeFileRepository $repoPackageTypeFilesRepo */
        $repoPackageTypeFilesRepo = $this->em->getRepository(RepositoryPackageTypeFile::class);

        $repoStats = $repoRepo->stats();
        $packageTypeFilesStats = $packageTypeFileRepo->stats();
        $repoPackageTypeFilesStats = $repoPackageTypeFilesRepo->stats();

        return $this->json([
            'packageTypeFiles' => $packageTypeFilesStats,
            'routines' => array_merge(['routine1Count' => $repoStats['routine2Count']], $repoStats, $repoPackageTypeFilesStats),
        ]);
    }

    #[Route(path: '/timeline', name: 'admin.timeline', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function timeline(): Response
    {
        /** @var RepositoryRepository $repoRepo */
        $repoRepo = $this->em->getRepository(Repository::class);
        /** @var RepositoryPackageTypeFileRepository $repoPackageTypeFilesRepo */
        $repoPackageTypeFilesRepo = $this->em->getRepository(RepositoryPackageTypeFile::class);

        // todo handle day, week, month
        $minDate = (new \DateTimeImmutable('-7 days'))->setTime(0, 0);
        $now = new \DateTimeImmutable();
        $d = $minDate;
        $labels = [];
        while ($d < $now) {
            $labels[] = $d->format('Y-m-d');
            $d = $d->add(new \DateInterval('P1D'));
        }

        return $this->json([
            'labels' => $labels,
            'routine1' => $repoRepo->timelineRoutine1($minDate),
            'routine2' => $repoRepo->timelineRoutine2($minDate),
            'routine3' => $repoPackageTypeFilesRepo->timelineRoutine3($minDate),
        ]);
    }
}
