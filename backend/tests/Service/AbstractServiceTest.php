<?php

namespace App\Tests\Service;

use App\Tests\TestUtilsTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractServiceTest extends KernelTestCase
{
    use TestUtilsTrait;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->purgeDatabase();

        // parent
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->terminateTest();
    }
}
