<?php

namespace App\Tests\Command;

use App\Entity\User;

class CreateUserCommandTest extends AbstractCommandTest
{
    /**
     * @dataProvider providerExecute
     */
    public function testExecute(bool $isAdmin, array $expectedRoles): void
    {
        $commandName = 'app:user:create';
        $commandTester = $this->getCommandTester($commandName);

        $commandTester->setInputs(['Test2000!']);
        $commandTester->execute([
            'command' => $commandName,
            'username' => 'test',
            'email' => 'test@mp3000.fr',
            '--is-admin' => $isAdmin,
        ]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('SUCCESS', $output);

        preg_match('/\(id=(\d+)\)/', $output, $arr);
        $id = (int) $arr[1];

        $user = $this->em->getRepository(User::class)->find($id);
        self::assertEquals($expectedRoles, $user->getRoles());
    }

    public function providerExecute(): array
    {
        return [
            'Is not admin' => [
                'isAdmin' => false,
                'expectedRoles' => ['ROLE_USER'],
            ],
            'Is admin' => [
                'isAdmin' => true,
                'expectedRoles' => ['ROLE_USER', 'ROLE_ADMIN'],
            ],
        ];
    }
}
