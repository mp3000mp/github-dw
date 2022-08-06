<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PackageTypeFile;
use App\Entity\Repository;
use App\Entity\RepositoryPackageTypeFile;
use App\Repository\PackageTypeFileRepository;
use App\Repository\RepositoryPackageTypeFileRepository;
use App\Repository\RepositoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    #[Route(path: '/errors', name: 'admin.errors', methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function errors(): Response
    {
        /** @var RepositoryRepository $repoRepo */
        $repoRepo = $this->em->getRepository(Repository::class);
        /** @var RepositoryPackageTypeFileRepository $repoPackageRepo */
        $repoPackageRepo = $this->em->getRepository(RepositoryPackageTypeFile::class);

        $d = new \DateTime('-30 days');
        $routine2Errors = $repoRepo->findErrors($d);
        $routine3Errors = $repoPackageRepo->findErrors($d);

        return $this->json([
            'routine2' => $routine2Errors,
            'routine3' => $routine3Errors,
        ]);
    }

    #[Route(path: '/stats', name: 'admin.stats', methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
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
    #[Security("is_granted('ROLE_ADMIN')")]
    public function timeline(): Response
    {
        /** @var RepositoryRepository $repoRepo */
        $repoRepo = $this->em->getRepository(Repository::class);
        /** @var RepositoryPackageTypeFileRepository $repoPackageTypeFilesRepo */
        $repoPackageTypeFilesRepo = $this->em->getRepository(RepositoryPackageTypeFile::class);

        // todo handle day, week, month
        $minDate = new \DateTime('-7 days');
        $minDate->setTime(0, 0);
        $d = clone $minDate;
        $labels = [];
        while ($d < new \DateTime()) {
            $labels[] = $d->format('Y-m-d');
            $d->add(new \DateInterval('P1D'));
        }

        return $this->json([
            'labels' => $labels,
            'routine1' => $repoRepo->timelineRoutine1($minDate),
            'routine2' => $repoRepo->timelineRoutine2($minDate),
            'routine3' => $repoPackageTypeFilesRepo->timelineRoutine3($minDate),
        ]);
    }
}
