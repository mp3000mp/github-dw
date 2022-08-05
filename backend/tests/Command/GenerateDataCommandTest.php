<?php

namespace App\Tests\Command;

class GenerateDataCommandTest extends AbstractCommandTest
{
    public function testExecute(): void
    {
        $commandName = 'app:generate-data';
        $commandTester = $this->getCommandTester($commandName);

        $commandTester->execute([
            'command' => $commandName,
            'number' => 100,
        ]);

        $output = $commandTester->getDisplay();
        self::assertEquals("This command should be used in DEV env only.\n", $output);
    }
}
