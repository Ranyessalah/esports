<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Training;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create Coach
        $coach = new User();
        $coach->setEmail('coach@test.com');
        $coach->setFirstName('Admin');
        $coach->setLastName('Coach');
        $coach->setRoles(['ROLE_COACH']);
        $coach->setPassword($this->passwordHasher->hashPassword($coach, '123456'));
        $manager->persist($coach);

        // Create Player 1
        $player1 = new User();
        $player1->setEmail('player1@test.com');
        $player1->setFirstName('John');
        $player1->setLastName('Doe');
        $player1->setRoles(['ROLE_PLAYER']);
        $player1->setPassword($this->passwordHasher->hashPassword($player1, '123456'));
        $manager->persist($player1);

        // Create Player 2
        $player2 = new User();
        $player2->setEmail('player2@test.com');
        $player2->setFirstName('Jane');
        $player2->setLastName('Smith');
        $player2->setRoles(['ROLE_PLAYER']);
        $player2->setPassword($this->passwordHasher->hashPassword($player2, '123456'));
        $manager->persist($player2);

        // Create sample training
        $training = new Training();
        $training->setTitle('Morning Drill');
        $training->setTheme('Passing');
        $training->setDescription('Intense passing accuracy training sessions for midfielders.');
        $training->setDate(new \DateTime('tomorrow'));
        $training->setStartTime(new \DateTime('09:00:00'));
        $training->setEndTime(new \DateTime('11:00:00'));
        $training->setLocation('Stadium Pitch A');
        $training->setCreatedBy($coach);
        $manager->persist($training);

        $manager->flush();
    }
}
