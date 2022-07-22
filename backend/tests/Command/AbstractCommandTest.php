<?php

namespace App\Tests\Command;

use App\Tests\TestUtilsTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCommandTest extends KernelTestCase
{
    use TestUtilsTrait;
    protected Application $application;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->application = new Application($kernel);

        $this->purgeDatabase();

        // parent
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->terminateTest();
    }

    protected function getCommandTester(string $commandName): CommandTester
    {
        $command = $this->application->find($commandName);

        return new CommandTester($command);
    }
}
