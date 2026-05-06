<?php

namespace App\Tests\Command;

use App\Command\GenerateDataCommand;
use App\Entity\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GenerateDataCommandTest extends AbstractCommandTest
{
    public function testExecute(): void
    {
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->method('get')->with('app.env')->willReturn('dev');

        $repoCountBefore = count($this->em->getRepository(Repository::class)->findAll());

        $commandTester = new CommandTester(new GenerateDataCommand($this->em, $parameterBag));
        $commandTester->execute(['number' => 100]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        self::assertStringContainsString('SUCCESS', $commandTester->getDisplay());
        self::assertSame($repoCountBefore + 100, count($this->em->getRepository(Repository::class)->findAll()));
    }
}
