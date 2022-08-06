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
        $this->client->request('GET', '/api/admin/timeline');
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
                'routine1Count' => 5,
                'routine2Count' => 5,
                'routine2DoneCount' => 4,
                'routine2ErrorCount' => 1,
                'routine3Count' => 7,
                'routine3DoneCount' => 6,
                'routine3ErrorCount' => 1,
            ],
        ];
        self::assertEqualsCanonicalizing($expected, $jsonResponse);
    }

    public function testTimeline(): void
    {
        $this->loginUser($this->client);

        $this->client->request('GET', '/api/admin/timeline');
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());

        $labels = [];
        $d = new \DateTime('-7 days');
        while ($d < new \DateTime()) {
            $labels[] = $d->format('Y-m-d');
            $d->add(new \DateInterval('P1D'));
        }

        self::assertEqualsCanonicalizing($labels, $jsonResponse['labels']);
        $expected = [
            ['label' => $labels[2], 'done' => 4],
            ['label' => $labels[4], 'done' => 1],
        ];
        self::assertEqualsCanonicalizing($expected, $jsonResponse['routine1']);
        $expected = [
            ['label' => $labels[3], 'done' => 3, 'errors' => 0],
            ['label' => $labels[6], 'done' => 1, 'errors' => 1],
        ];
        self::assertEqualsCanonicalizing($expected, $jsonResponse['routine2']);
        $expected = [
            ['label' => $labels[4], 'done' => 5, 'errors' => 0],
            ['label' => $labels[5], 'done' => 1, 'errors' => 1],
        ];
        self::assertEqualsCanonicalizing($expected, $jsonResponse['routine3']);
    }

    private function getPackageTypeByFile(string $file): PackageTypeFile
    {
        return $this->em->getRepository(PackageTypeFile::class)->findOneBy(['file' => $file]);
    }
}
