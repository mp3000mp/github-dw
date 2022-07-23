<?php

namespace App\Tests\Controller;

use App\Entity\User;

class UserControllerTest extends AbstractControllerTest
{
    public function testIndexOk(): void
    {
        $this->loginUser($this->client);

        $this->client->request('GET', '/api/users');
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());

        $user = $this->em->getRepository(User::class)->findOneBy(['username' => 'admin']);

        self::assertCount(1, $jsonResponse);
        self::assertEquals([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'isEnabled' => $user->getIsEnabled(),
            'roles' => $user->getRoles(),
            'isSuperAdmin' => $user->getIsSuperAdmin(),
        ], $jsonResponse[0]);
    }

    public function testShowOk(): void
    {
        $this->loginUser($this->client);

        $user = $this->getUser('admin');
        $this->client->request('GET', sprintf('/api/users/%s', $user->getId()));

        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());

        self::assertEquals('admin', $jsonResponse['username']);
    }

    public function testRoles(): void
    {
        $user = $this->getUser('admin');

        $this->client->request('GET', '/api/users');
        $this->assertResponseCode(401);
        $this->client->request('GET', sprintf('/api/users/%s', $user->getId()));
        $this->assertResponseCode(401);
    }

    private function getUser(string $username): User
    {
        return $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
    }
}
