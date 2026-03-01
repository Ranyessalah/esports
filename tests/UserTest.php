<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testInvalidEmail(): void
    {
        $user = new User();
        $user->setEmail("notanemail");
        $user->setPassword("password123");

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, count($errors));
    }

    public function testPasswordTooShort(): void
    {
        $user = new User();
        $user->setEmail("user@test.com");
        $user->setPassword("123");

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, count($errors));
    }
}