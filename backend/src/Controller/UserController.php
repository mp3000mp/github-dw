<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users')]
class UserController extends AbstractController
{
    #[Route(path: '', name: 'users.index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $users = $this->em->getRepository(User::class)->findAll();

        return $this->responseHelper->createResponse($users, ['admin'], 200);
    }

    #[Route(path: '/{id}', name: 'users.show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(User $user): Response
    {
        return $this->responseHelper->createResponse($user, ['admin'], 200);
    }
}
