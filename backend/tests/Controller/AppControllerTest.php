<?php

namespace App\Tests\Controller;

class AppControllerTest extends AbstractControllerTest
{
    public function testInfo(): void
    {
        $this->client->request('GET', '/api/info');
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());

        self::assertArrayHasKey('version', $jsonResponse);
    }

    public function testMeOk(): void
    {
        $this->loginUser($this->client);

        $this->client->request('GET', '/api/me');
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());

        self::assertEquals([
            'username' => 'admin',
            'roles' => ['ROLE_ADMIN', 'ROLE_USER'],
        ], $jsonResponse);
    }
}
