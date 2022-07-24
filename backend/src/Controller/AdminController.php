<?php

declare(strict_types=1);

namespace App\Controller;

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

        $errors2 = array_map(function (array $error) {
            $error['path'] = null;
            $error['routine'] = 2;

            return $error;
        }, $routine2Errors);
        $errors3 = array_map(function (array $error) {
            $error['routine'] = 3;

            return $error;
        }, $routine3Errors);

        return $this->json(array_merge($errors2, $errors3));
    }
}
