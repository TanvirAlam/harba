<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetId(): void
    {
        $user = new User();
        $this->assertNull($user->getId());
    }

    public function testSetAndGetEmail(): void
    {
        $user = new User();
        $email = 'test@example.com';
        $user->setEmail($email);
        $this->assertEquals($email, $user->getEmail());
    }

    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $email = 'test@example.com';
        $user->setEmail($email);
        $this->assertEquals($email, $user->getUserIdentifier());
    }

    public function testSetAndGetRoles(): void
    {
        $user = new User();
        $roles = ['ROLE_ADMIN'];
        $user->setRoles($roles);
        $expectedRoles = array_unique(array_merge($roles, ['ROLE_USER']));
        $this->assertEquals($expectedRoles, $user->getRoles());
    }

    public function testGetRolesAddsDefaultRoleUser(): void
    {
        $user = new User();
        $user->setRoles([]);
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testSetAndGetPassword(): void
    {
        $user = new User();
        $password = 'hashedpassword';
        $user->setPassword($password);
        $this->assertEquals($password, $user->getPassword());
    }
}