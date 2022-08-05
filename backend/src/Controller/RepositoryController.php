<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Repository;
use App\Repository\RepositoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/repositories')]
class RepositoryController extends AbstractController
{
    #[Route(path: '/search', name: 'repositories.search', methods: ['POST'])]
    public function search(Request $request, SerializerInterface $serializer): Response
    {
        /** @var RepositoryRepository $repoRepo */
        $repoRepo = $this->em->getRepository(Repository::class);

        $search = $this->requestHelper->handleRequest($request->getContent(), 'repository_search');
        $total = $repoRepo->countWithQuery($search->search);
        $results = [];
        if ($total > 0) {
            $limit = min(10, $search->perPage);
            $offset = ($search->page - 1) * $limit;
            $results = $repoRepo->findWithQuery($search->search, $offset, $limit);
        }

        return $this->json([
            'results' => 0 === $total ? [] : json_decode($serializer->serialize($results, 'json', ['groups' => ['all']])),
            'total' => $total,
        ]);
    }
}
