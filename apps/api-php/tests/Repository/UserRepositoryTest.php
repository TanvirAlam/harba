<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testUpgradePassword(): void
    {
        $user = new User();
        $user->setEmail('repo' . time() . '@example.com');
        $user->setPassword('oldpassword');

        $newHashedPassword = 'newhashedpassword';

        $this->userRepository->upgradePassword($user, $newHashedPassword);

        $this->assertEquals($newHashedPassword, $user->getPassword());
    }

    public function testUpgradePasswordThrowsExceptionForUnsupportedUser(): void
    {
        $unsupportedUser = new \stdClass();

        $this->expectException(\TypeError::class);
        $this->userRepository->upgradePassword($unsupportedUser, 'password');
    }
}