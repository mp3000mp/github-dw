<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\TestUtilsTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractControllerTest extends WebTestCase
{
    use TestUtilsTrait;
    private array $userByRole = [
        'ROLE_USER' => 'user',
        'ROLE_ADMIN' => 'admin',
    ];

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->setServerParameter('CONTENT_TYPE', 'application/json');

        $this->purgeDatabase();

        // parent
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->terminateTest();
    }

    protected function loginUser(KernelBrowser $client, string $role = 'ROLE_USER'): void
    {
        $userRepository = $this->em->getRepository(User::class);
        // $userRepository = static::$container->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['username' => $this->userByRole[$role]]);
        $client->request(
            'POST',
            '/api/logincheck',
            [],
            [],
            [],
            json_encode([
                'username' => $testUser->getUsername(),
                'password' => 'Test2000!',
            ])
        );
        $data = json_decode($client->getResponse()->getContent(), true);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));
    }

    protected function getResponseJson(Response $response): array
    {
        return json_decode($response->getContent(), true);
    }

    protected function dumpResponse(): void
    {
        echo $this->client->getResponse()->getContent();
    }
}
