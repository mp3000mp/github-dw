<?php

namespace App\Tests\Controller;

use App\Entity\Repository;

class RepositoryControllerTest extends AbstractControllerTest
{
    /**
     * @dataProvider providerSearch
     */
    public function testSearch(array $query, int $expectedTotal): void
    {
        // no results
        $query = [
            'page' => 1,
            'search' => $query,
        ];
        $this->client->request('POST', '/api/repositories/search', [], [], [], json_encode($query));
        $this->assertResponseCode(200);
        $jsonResponse = $this->getResponseJson($this->client->getResponse());
        self::assertCount($expectedTotal, $jsonResponse['results']);
        self::assertEquals($expectedTotal, $jsonResponse['total']);
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
                'expectedTotal' => 0,
            ],
            'Name query' => [
                'query' => [
                    'name' => 'name',
                    'description' => null,
                    'packages' => [],
                ],
                'expectedRoles' => 2,
            ],
            'Description query' => [
                'query' => [
                    'name' => null,
                    'description' => 'descri',
                    'packages' => [],
                ],
                'expectedRoles' => 2,
            ],
            'Package id query' => [
                'query' => [
                    'name' => null,
                    'description' => null,
                    'packages' => [['id' => 1, 'minVersion' => null, 'maxVersion' => null]],
                ],
                'expectedRoles' => 2,
            ],
            'Package version query' => [
                'query' => [
                    'name' => null,
                    'description' => null,
                    'packages' => [['id' => 1, 'minVersion' => '1.0.0', 'maxVersion' => '2.0.0']],
                ],
                'expectedRoles' => 2,
            ],
        ];
    }

    private function getRepositoryByName(string $name): Repository
    {
        return $this->em->getRepository(Repository::class)->findOneBy(['name' => $name]);
    }
}
