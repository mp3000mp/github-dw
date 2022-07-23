<?php

namespace App\Tests\Controller;

class AdminControllerTest extends AbstractControllerTest
{
    public function testAuth(): void
    {
        $this->client->request('GET', '/api/admin/errors');
        $this->assertResponseCode(401);
    }

    public function testErrors(): void
    {
        $this->loginUser($this->client);

        $this->client->request('GET', '/api/admin/errors');
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        self::assertCount(2, $jsonResponse);
        self::assertEquals(2, $jsonResponse[0]['routine']);
        self::assertEquals('error2', $jsonResponse[0]['error']);
        self::assertEquals(3, $jsonResponse[1]['routine']);
        self::assertEquals('error3', $jsonResponse[1]['error']);
    }
}
