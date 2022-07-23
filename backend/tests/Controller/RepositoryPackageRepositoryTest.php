<?php

namespace App\Tests\Controller;

class RepositoryPackageRepositoryTest extends AbstractControllerTest
{
    public function testAutocomplete(): void
    {
        $this->client->request('POST', '/api/repository-packages/autocomplete', [], [], [], json_encode(['language' => 'PHP', 'text' => 'repoPa']));
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        self::assertCount(2, $jsonResponse);
        self::assertEquals('repoPackageA', $jsonResponse[0]['name']);
    }
}
