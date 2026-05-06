<?php

namespace App\Tests\Command;

use Symfony\Component\Console\Command\Command;

class UselessDataCleanupCommandTest extends AbstractCommandTest
{
    public function testExecute(): void
    {
        $commandName = 'app:data:cleanup';
        $commandTester = $this->getCommandTester($commandName);

        $commandTester->execute([
            'command' => $commandName,
            '--force-flush' => true,
            '--min-packages' => 3,
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        self::assertStringContainsString('FLUSH MODE - MIN=3', $commandTester->getDisplay());
        self::assertStringContainsString('SUCCESS', $commandTester->getDisplay());
    }
}
