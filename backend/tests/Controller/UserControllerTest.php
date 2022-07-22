<?php

namespace App\Tests\Controller;

use App\Entity\User;

class UserControllerTest extends AbstractControllerTest
{
    private function getUserId(string $username): int
    {
        return $this->em->getRepository(User::class)->findOneBy(['username' => $username])->getId();
    }

    public function testIndexOk(): void
    {
        $this->loginUser($this->client, 'ROLE_ADMIN');

        $this->client->request('GET', '/api/users');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $jsonResponse = $this->getResponseJson($this->client->getResponse());

        $user = $this->em->getRepository(User::class)->findOneBy(['username' => 'user']);

        self::assertCount(3, $jsonResponse);
        self::assertEquals([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'isEnabled' => $user->getIsEnabled(),
            'roles' => $user->getRoles(),
        ], $jsonResponse[0]);
    }

    public function testShowOk(): void
    {
        $this->loginUser($this->client, 'ROLE_ADMIN');

        $id = $this->getUserId('user');
        $this->client->request('GET', "/api/users/$id");

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $jsonResponse = $this->getResponseJson($this->client->getResponse());

        self::assertEquals('user', $jsonResponse['username']);
    }

    public function testRoles(): void
    {
        $this->loginUser($this->client);

        $id = $this->getUserId('user');

        $this->client->request('GET', '/api/users');
        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->client->request('GET', "/api/users/$id");
        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
}
