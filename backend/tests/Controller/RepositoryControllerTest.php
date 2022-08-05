<?php

namespace App\Tests\Controller;

use App\Entity\Package;

class RepositoryControllerTest extends AbstractControllerTest
{
    /**
     * @dataProvider providerSearch
     */
    public function testSearch(array $query, array $expectedResult): void
    {
        // find package ids by name
        foreach ($query['packages'] as &$package) {
            $package['id'] = $this->getPackageByName($package['id'])->getId();
        }
        unset($package);
        $query = [
            'page' => 1,
            'perPage' => 10,
            'search' => $query,
        ];
        $this->client->request('POST', '/api/repositories/search', [], [], [], json_encode($query));
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        self::assertEqualsCanonicalizing($expectedResult, array_column($jsonResponse['results'], 'name'));
        self::assertEquals(count($expectedResult), $jsonResponse['total']);
    }

    public function providerSearch(): array
    {
        return [
            'No result' => [
                'query' => [
                    'name' => 'zzz',
                    'description' => null,
                    'packages' => [],
                ],
                'expectedResult' => [],
            ],
            'Name query' => [
                'query' => [
                    'name' => 'repo',
                    'description' => null,
                    'packages' => [],
                ],
                'expectedResult' => ['repoA', 'repoB', 'repoC'],
            ],
            'Description query' => [
                'query' => [
                    'name' => null,
                    'description' => 'descri',
                    'packages' => [],
                ],
                'expectedResult' => ['repoA', 'repoB', 'repoC'],
            ],
            'Package id query' => [
                'query' => [
                    'name' => null,
                    'description' => null,
                    'packages' => [['id' => 'packageC', 'minVersion' => null, 'maxVersion' => null]],
                ],
                'expectedResult' => ['repoA', 'repoC'],
            ],
            'Package version query' => [
                'query' => [
                    'name' => null,
                    'description' => null,
                    'packages' => [
                        ['id' => 'packageA', 'minVersion' => '0.5.0', 'maxVersion' => '1.1.0'], // A,B,C
                        ['id' => 'packageB', 'minVersion' => '1.7.0', 'maxVersion' => '3.0.0'], // C
                        ['id' => 'packageC', 'minVersion' => '0.5.0', 'maxVersion' => '3.0.0'], // B,C
                    ],
                ],
                'expectedResult' => ['repoC'],
            ],
        ];
    }

    private function getPackageByName(string $name): Package
    {
        return $this->em->getRepository(Package::class)->findOneBy(['name' => $name]);
    }
}
