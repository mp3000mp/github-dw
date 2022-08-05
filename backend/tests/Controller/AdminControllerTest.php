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
        self::assertCount(1, $jsonResponse['routine2']);
        self::assertEquals('error2', $jsonResponse['routine2'][0]['error']);
        self::assertCount(1, $jsonResponse['routine3']);
        self::assertEquals('error3', $jsonResponse['routine3'][0]['error']);
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
