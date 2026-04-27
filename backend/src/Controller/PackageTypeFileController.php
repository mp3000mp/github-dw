<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PackageTypeFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/package-type-files')]
class PackageTypeFileController extends AbstractController
{
    #[Route(path: '', name: 'package_type_files.index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $packageTypes = $this->em->getRepository(PackageTypeFile::class)->findAll();

        return $this->responseHelper->createResponse($packageTypes, ['admin']);
    }

    #[Route(path: '/{id}/priority', name: 'package_type_files.priority', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
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
