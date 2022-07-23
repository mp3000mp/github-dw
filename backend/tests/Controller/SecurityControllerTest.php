<?php

namespace App\Tests\Controller;

class SecurityControllerTest extends AbstractControllerTest
{
    public function testLoginError(): void
    {
        $this->client->request('GET', '/api/login');
        $this->assertResponseCode(405);

        $this->client->request('POST', '/api/login');
        $this->assertResponseCode(401);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        self::assertEquals('Invalid form.', $jsonResponse['message']);

        $this->client->request('POST', '/api/login', [], [], [], json_encode(['unknown' => 'unknown']));
        $this->assertResponseCode(401);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        self::assertEquals('Invalid form.', $jsonResponse['message']);

        $this->client->request('POST', '/api/login', [], [], [], json_encode(['username' => 'unknown', 'password' => 'unknown']));
        $this->assertResponseCode(401);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        self::assertEquals('Bad credentials.', $jsonResponse['message']);
    }
}
