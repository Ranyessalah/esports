<?php

namespace App\Tests\Repository;

use App\Entity\Equipe;
use App\Entity\User;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EquipeRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $em = null;
        private EquipeRepository $repo;

        protected function setUp(): void
        {
            self::bootKernel();
        
            $this->em = static::getContainer()->get(EntityManagerInterface::class);
            $this->repo = $this->em->getRepository(Equipe::class);
        
            // 🔴 CLEAN DATABASE BEFORE EACH TEST
            $this->em->createQuery('DELETE FROM App\Entity\Matchs')->execute();
            $this->em->createQuery('DELETE FROM App\Entity\Equipe')->execute();
            $this->em->createQuery('DELETE FROM App\Entity\User')->execute();
        }

    private function createEquipe(string $name, string $game): Equipe
    {
        $coach = new User();
        $coach->setEmail($name.'@test.com');
        $coach->setPassword('password123');

        $equipe = new Equipe();
        $equipe->setNom($name);
        $equipe->setGame($game);
        $equipe->setCategorie('ProTeam');
        $equipe->setLogo('logo.png');
        $equipe->setCoach($coach);

        $this->em->persist($coach);
        $this->em->persist($equipe);
        $this->em->flush();

        return $equipe;
    }

    public function testFindAllWithRelations(): void
    {
        $this->createEquipe("TeamAlpha", "LoL");

        $result = $this->repo->findAllWithRelations();

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(Equipe::class, $result[0]);
    }

    public function testFindAllWithSearch(): void
    {
        $this->createEquipe("ValorantKings", "Valorant");

        $result = $this->repo->findAllWithSearch("ValorantKings", null, null);

        $this->assertCount(1, $result);
        $this->assertEquals("ValorantKings", $result[0]->getNom());
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