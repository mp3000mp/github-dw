<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Serializer\SerializerInterface;

class Authenticator extends AbstractLoginFormAuthenticator
{
    private SerializerInterface $serializer;
    private RateLimiterFactory $loginRouteLimiter;

    public function __construct(SerializerInterface $serializer, RateLimiterFactory $loginRouteLimiter)
    {
        $this->serializer = $serializer;
        $this->loginRouteLimiter = $loginRouteLimiter;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse([
            'Access denied.',
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function getLoginUrl(Request $request): string
    {
        return '/api/login';
    }

    public function authenticate(Request $request): Passport
    {
        $limiter = $this->loginRouteLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            throw new AuthenticationException('Too many attempts, please try later.');
        }

        $content = json_decode($request->getContent(), true);
        $password = $request->request->get('password')
            ?? $content['password'] ?? null;
        $username = $request->request->get('username')
            ?? $content['username'] ?? null;

        if (null === $password || null === $username) {
            throw new AuthenticationException('Invalid form.');
        }

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($password),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new JsonResponse([
            'message' => 'Welcome.',
            'me' => json_decode($this->serializer->serialize($token->getUser(), 'json', ['groups' => ['me']])),
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new JsonResponse([
            'message' => $exception->getMessage(),
        ], 'Too many attempts, please try later.' === $exception->getMessage() ? Response::HTTP_TOO_MANY_REQUESTS : Response::HTTP_UNAUTHORIZED);
    }
}
