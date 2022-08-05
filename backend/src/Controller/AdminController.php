<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PackageTypeFile;
use App\Entity\Repository;
use App\Entity\RepositoryPackageTypeFile;
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
        $packageTypeFilesStats = $this->em->getRepository(PackageTypeFile::class)->stats();
        $repoStats = $this->em->getRepository(Repository::class)->stats();
        $repoPackageTypeFilesStats = $this->em->getRepository(RepositoryPackageTypeFile::class)->stats();

        return $this->json([
            'packageTypeFiles' => $packageTypeFilesStats,
            'routines' => array_merge(['routine1Count' => $repoStats['routine2Count']], $repoStats, $repoPackageTypeFilesStats),
        ]);
    }
}
