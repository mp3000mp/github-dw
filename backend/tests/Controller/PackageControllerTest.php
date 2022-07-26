<?php

namespace App\Tests\Controller;

class PackageControllerTest extends AbstractControllerTest
{
    public function testAutocomplete(): void
    {
        $this->client->request('POST', '/api/packages/autocomplete', [], [], [], json_encode(['language' => 'PHP', 'text' => 'pack']));
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        self::assertCount(2, $jsonResponse);
        self::assertEquals('packageA', $jsonResponse[0]['name']);
    }
}
