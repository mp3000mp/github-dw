<?php

namespace App\Tests\Controller;

use App\Entity\PackageTypeFile;

class AdminControllerTest extends AbstractControllerTest
{
    public function testAuth(): void
    {
        $this->client->request('GET', '/api/admin/errors');
        $this->assertResponseCode(401);
        $this->client->request('GET', '/api/admin/stats');
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

    public function testStats(): void
    {
        $this->loginUser($this->client);

        $this->client->request('GET', '/api/admin/stats');
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        $expected = [
            'packageTypeFiles' => [
                ['id' => $this->getPackageTypeByFile('composer.json')->getId(), 'count' => 2],
                ['id' => $this->getPackageTypeByFile('package.json')->getId(), 'count' => 1],
            ],
            'routines' => [
                'routine1Count' => 4,
                'routine2Count' => 4,
                'routine2DoneCount' => 3,
                'routine2ErrorCount' => 1,
                'routine3Count' => 6,
                'routine3DoneCount' => 0,
                'routine3ErrorCount' => 1,
            ],
        ];
        self::assertEqualsCanonicalizing($expected, $jsonResponse);
    }

    private function getPackageTypeByFile(string $file): PackageTypeFile
    {
        return $this->em->getRepository(PackageTypeFile::class)->findOneBy(['file' => $file]);
    }
}
