<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Package;
use App\Repository\PackageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/packages')]
class PackageController extends AbstractController
{
    #[Route(path: '/autocomplete', name: 'packages.autocomplete', methods: ['POST'])]
    public function autocomplete(Request $request): Response
    {
        /** @var PackageRepository $repoPackageRepo */
        $repoPackageRepo = $this->em->getRepository(Package::class);

        $search = $this->requestHelper->handleRequest($request->getContent(), 'package_autocomplete');
        $results = $repoPackageRepo->autocomplete($search->language, $search->text);

        return $this->responseHelper->createResponse($results, ['autocomplete']);
    }
}
