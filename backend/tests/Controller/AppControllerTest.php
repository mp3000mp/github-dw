<?php

namespace App\Tests\Controller;

class AppControllerTest extends AbstractControllerTest
{
    public function testInfo(): void
    {
        $this->client->request('GET', '/api/info');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $jsonResponse = $this->getResponseJson($this->client->getResponse());

        self::assertArrayHasKey('version', $jsonResponse);
    }

    public function testMeOk(): void
    {
        $this->loginUser($this->client);

        $this->client->request('GET', '/api/me');

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
        $jsonResponse = $this->getResponseJson($this->client->getResponse());

        self::assertEquals([
            'username' => 'user',
            'roles' => ['ROLE_USER'],
        ], $jsonResponse);
    }
}
