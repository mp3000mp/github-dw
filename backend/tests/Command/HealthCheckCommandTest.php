<?php

namespace App\Tests\Command;

use Symfony\Component\Console\Command\Command;

class HealthCheckCommandTest extends AbstractCommandTest
{
    public function testExecute(): void
    {
        $commandName = 'app:health-check';
        $commandTester = $this->getCommandTester($commandName);
        $commandTester->execute(['command' => $commandName]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        self::assertStringContainsString('SUCCESS', $commandTester->getDisplay());
    }
}
