<?php

namespace App\Tests\Controller;

use App\Entity\PackageTypeFile;
use App\Entity\User;

class PackageTypeFileControllerTest extends AbstractControllerTest
{
    public function testAuth(): void
    {
        $this->client->request('GET', '/api/package-type-file');
        $this->assertResponseCode(401);
        $pft = $this->getPackageTypeFile('composer.json');
        $this->client->request('PUT', '/api/package-type-file/'.$pft->getId().'/priority');
        $this->assertResponseCode(401);
    }

    public function testIndex(): void
    {
        $this->loginUser($this->client);

        $this->client->request('GET', '/api/package-type-file');
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        self::assertCount(2, $jsonResponse);
    }

    public function testPriority(): void
    {
        $this->loginUser($this->client);

        $pft = $this->getPackageTypeFile('package.json');
        $this->client->request('PUT', '/api/package-type-file/'.$pft->getId().'/priority');
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        self::assertCount(2, $jsonResponse);
        self::assertEquals(false, $jsonResponse[0]['priority']);
        self::assertEquals(true, $jsonResponse[1]['priority']);
    }

    private function getPackageTypeFile(string $file): PackageTypeFile
    {
        return $this->em->getRepository(PackageTypeFile::class)->findOneBy(['file' => $file]);
    }
}
