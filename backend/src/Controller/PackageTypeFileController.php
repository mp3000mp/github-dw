<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PackageTypeFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/package-type-file')]
class PackageTypeFileController extends AbstractController
{
    #[Route(path: '', name: 'package_type_file.index', methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function index(): Response
    {
        $packageTypes = $this->em->getRepository(PackageTypeFile::class)->findAll();

        return $this->responseHelper->createResponse($packageTypes, ['admin']);
    }

    #[Route(path: '/{id}/priority', name: 'package_type_file.priority', methods: ['PUT'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function priority(int $id): Response
    {
        $packageTypes = $this->em->getRepository(PackageTypeFile::class)->findAll();
        foreach ($packageTypes as $packageType) {
            $packageType->setPriority($packageType->getId() === $id);
        }
        $this->em->flush();

        return $this->responseHelper->createResponse($packageTypes, ['admin']);
    }
}
