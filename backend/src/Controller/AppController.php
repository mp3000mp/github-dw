<?php

declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class AppController extends AbstractController
{
    #[Route(path: '/info', name: 'app.info', methods: ['GET'])]
    public function info(ParameterBagInterface $parameterBag): Response
    {
        return $this->json([
            'version' => $parameterBag->get('APP_VERSION'),
        ]);
    }

    #[Route(path: '/me', name: 'users.me', methods: ['GET'])]
    #[Security("is_granted('ROLE_USER')")]
    public function me(): Response
    {
        return $this->responseHelper->createResponse($this->getUser(), ['me'], 200);
    }

    #[Route(path: '/404', name: 'app.404', methods: ['GET'])]
    public function error404(Request $request, DebugLoggerInterface $logger): Response
    {
        $err = $request->get('exception');

        $msg = $err->getMessage();
        // $msg = 'Server error.';
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
