<?php

namespace App\Tests;

use App\Entity\Player;
use App\Enum\Niveau;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlayerTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    private function getValidPlayer(): Player
    {
        $player = new Player();
        $player->setEmail("player@test.com");
        $player->setPassword("password123");
        $player->setPays("Tunisie");
        $player->setNiveau(Niveau::BEGINNER);

        return $player;
    }

    public function testValidPlayer(): void
    {
        $player = $this->getValidPlayer();
        $errors = $this->validator->validate($player);

        $this->assertCount(0, $errors);
    }

    public function testPlayerPaysBlank(): void
    {
        $player = $this->getValidPlayer();
        $player->setPays("");

        $errors = $this->validator->validate($player);

        $this->assertGreaterThan(0, count($errors));
    }

    public function testPlayerNiveauNull(): void
    {
        $player = $this->getValidPlayer();
        $player->setNiveau(null);

        $errors = $this->validator->validate($player);

        $this->assertGreaterThan(0, count($errors));
    }
}