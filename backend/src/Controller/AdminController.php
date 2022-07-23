<?php

declare(strict_types=1);

namespace App\Controller;

use _PHPStan_59fb0a3b2\Nette\Utils\DateTime;
use App\Entity\Repository;
use App\Entity\RepositoryPackageTypeFile;
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
        // todo filter errorDate > x
        $d = new DateTime('-30 days');
        $routine2Errors = $this->em->getRepository(Repository::class)->findErrors($d);
        $routine3Errors = $this->em->getRepository(RepositoryPackageTypeFile::class)->findErrors($d);

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
