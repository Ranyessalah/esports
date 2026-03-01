<?php

namespace App\Tests\Repository;

use App\Entity\Equipe;
use App\Entity\Matchs;
use App\Entity\User;
use App\Repository\MatchsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MatchsRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;
    private MatchsRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();
    
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repo = $this->em->getRepository(Matchs::class);    
        // 🔴 CLEAN DATABASE BEFORE EACH TEST
        $this->em->createQuery('DELETE FROM App\Entity\Matchs')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Equipe')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\User')->execute();
    }
    private function createMatch(): void
    {
        $coach = new User();
        $coach->setEmail("coach@test.com");
        $coach->setPassword("password123");

        $team1 = new Equipe();
        $team1->setNom("Team1")->setGame("LoL")->setCategorie("ProTeam")->setLogo("logo.png")->setCoach($coach);

        $team2 = new Equipe();
        $team2->setNom("Team2")->setGame("LoL")->setCategorie("ProTeam")->setLogo("logo.png")->setCoach($coach);

        $match = new Matchs();
        $match->setNomMatch("Final");
        $match->setStatut("termine");
        $match->setDateMatch(new \DateTime('-2 days'));
        $match->setDateFinMatch(new \DateTime('-1 day'));
        $match->setScoreEquipe1(2);
        $match->setScoreEquipe2(1);
        $match->setEquipe1($team1);
        $match->setEquipe2($team2);

        $this->em->persist($coach);
        $this->em->persist($team1);
        $this->em->persist($team2);
        $this->em->persist($match);
        $this->em->flush();
    }

    public function testGetMatchsWithScores(): void
    {
        $this->createMatch();

        $matches = $this->repo->getMatchsWithScores();

        $this->assertNotEmpty($matches);
    }

    public function testCountMatchesByStatus(): void
    {
        $this->createMatch();

        $result = $this->repo->countMatchesByStatus();

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
    }

    public function testGetTeamStats(): void
    {
        $this->createMatch();

        $stats = $this->repo->getTeamStats();

        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
    }
    protected function tearDown(): void
    {
        parent::tearDown();
    
        if ($this->em !== null) {
            $this->em->clear();
            $this->em->close();
            $this->em = null;
        }
    }

}