<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{
    public function testValidUser()
    {
        $user = new User();
        $user->setEmail('test@gmail.com');
        $user->setPlainPassword('Password@1');

        $manager = new UserManager();

        $this->assertTrue($manager->validate($user));
    }

    public function testInvalidEmail()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setEmail('rani.esprit');
        $user->setPlainPassword('Password@1');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testPasswordTooShort()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setEmail('test@gmail.com');
        $user->setPlainPassword('Pass@1');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testPasswordWithoutUppercase()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setEmail('test@gmail.com');
        $user->setPlainPassword('password@1');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testPasswordWithoutSpecialCharacter()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setEmail('test@gmail.com');
        $user->setPlainPassword('password1');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testBlockedUserCannotEnableTotp()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new User();
        $user->setEmail('test@gmail.com');
        $user->setPlainPassword('Password@1');
        $user->setIsBlocked(true);
        $user->setIsTotpEnabled(true);

        $manager = new UserManager();
        $manager->validate($user);
    }
}