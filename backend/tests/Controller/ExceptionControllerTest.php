<?php

namespace App\Tests\Controller;

class ExceptionControllerTest extends AbstractControllerTest
{
    public function test404(): void
    {
        $this->client->request('GET', '/api/does/not/exist');
        $this->assertResponseCode(404);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        self::assertEquals('No route found for "GET http://localhost/api/does/not/exist"', $jsonResponse['message']);
    }
}
