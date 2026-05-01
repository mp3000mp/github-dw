<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class AppController extends AbstractController
{
    #[Route(path: '/info', name: 'app.info', methods: ['GET'])]
    public function info(ParameterBagInterface $parameterBag): Response
    {
        return $this->json([
            'version' => $parameterBag->get('app.version'),
        ]);
    }

    #[Route(path: '/me', name: 'users.me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(): Response
    {
        return $this->responseHelper->createResponse($this->getUser(), ['me'], 200);
    }

    #[Route(path: '/404', name: 'app.404', methods: ['GET'])]
    public function error404(Request $request): Response
    {
        $err = $request->attributes->get('exception');

        //        if ($this->getParameter('app.env') === 'test') {
        //            dump($err);
        //        }

        $msg = 'Server error.';
        if ('prod' !== $this->getParameter('app.env')) {
            $msg = $err->getMessage();
        }
        $statusCode = 500;
        if (method_exists($err, 'getStatusCode')) {
            /** @phpstan-ignore-next-line */
            $msg = $err->getMessage();
            $statusCode = $err->getStatusCode();
        }

        return $this->json([
            'message' => $msg,
        ], $statusCode);
    }
}
