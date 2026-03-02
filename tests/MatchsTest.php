<?php

namespace App\Tests;

use App\Entity\Matchs;
use App\Entity\Equipe;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MatchsTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    private function getValidMatch(): Matchs
    {
        $equipe1 = new Equipe();
        $equipe1->setNom("Team A")->setGame("LoL")->setCategorie("ProTeam")->setLogo("logo.png");

        $equipe2 = new Equipe();
        $equipe2->setNom("Team B")->setGame("LoL")->setCategorie("ProTeam")->setLogo("logo.png");

        $match = new Matchs();
        $match->setNomMatch("Finale");
        $match->setDateMatch(new \DateTime('+2 days'));
        $match->setDateFinMatch(new \DateTime('+3 days'));
        $match->setEquipe1($equipe1);
        $match->setEquipe2($equipe2);

        return $match;
    }

    public function testValidMatch(): void
    {
        $match = $this->getValidMatch();
        $errors = $this->validator->validate($match);

        $this->assertCount(0, $errors);
    }

    public function testMatchDateInvalid(): void
    {
        $match = $this->getValidMatch();
        $match->setDateFinMatch(new \DateTime('-1 day')); // invalid

        $errors = $this->validator->validate($match);

        $this->assertGreaterThan(0, count($errors));
    }
}