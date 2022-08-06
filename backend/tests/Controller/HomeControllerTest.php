<?php

namespace App\Tests\Controller;

class HomeControllerTest extends AbstractControllerTest
{
    public function testHome(): void
    {
        $this->client->request('GET', '/');
        $this->assertResponseCode(302);
    }
}
