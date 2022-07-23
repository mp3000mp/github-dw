<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users')]
class UserController extends AbstractController
{
    #[Route(path: '', name: 'users.index', methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function index(): Response
    {
        $users = $this->em->getRepository(User::class)->findAll();

        return $this->responseHelper->createResponse($users, ['admin'], 200);
    }

    #[Route(path: '/{id}', name: 'users.show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function show(User $user): Response
    {
        return $this->responseHelper->createResponse($user, ['admin'], 200);
    }
}
