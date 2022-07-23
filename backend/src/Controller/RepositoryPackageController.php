<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RepositoryPackage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/repository-packages')]
class RepositoryPackageController extends AbstractController
{
    #[Route(path: '/autocomplete', name: 'packages.autocomplete', methods: ['POST'])]
    public function autocomplete(Request $request): Response
    {
        $search = $this->requestHelper->handleRequest($request->getContent(), 'repository_package_autocomplete');
        $results = $this->em->getRepository(RepositoryPackage::class)->autocomplete($search->language, $search->text);

        return $this->json($results);
    }
}
