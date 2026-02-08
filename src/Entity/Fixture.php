<?php

namespace App\Entity;

use App\Repository\FixtureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FixtureRepository::class)]
class Fixture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Relation to League
    #[ORM\ManyToOne(targetEntity: League::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?League $league = null;

    #[ORM\Column]
    private ?\DateTime $matchDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $scoreTeam1 = null;

    #[ORM\Column(nullable: true)]
    private ?int $scoreTeam2 = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $round = null;

    // ---------------- Getters & Setters ----------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLeague(): ?League
    {
        return $this->league;
    }

    public function setLeague(?League $league): static
    {
        $this->league = $league;
        return $this;
    }

    public function getMatchDate(): ?\DateTime
    {
        return $this->matchDate;
    }

    public function setMatchDate(\DateTime $matchDate): static
    {
        $this->matchDate = $matchDate;
        return $this;
    }

    public function getScoreTeam1(): ?int
    {
        return $this->scoreTeam1;
    }

    public function setScoreTeam1(?int $scoreTeam1): static
    {
        $this->scoreTeam1 = $scoreTeam1;
        return $this;
    }

    public function getScoreTeam2(): ?int
    {
        return $this->scoreTeam2;
    }

    public function setScoreTeam2(?int $scoreTeam2): static
    {
        $this->scoreTeam2 = $scoreTeam2;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getRound(): ?string
    {
        return $this->round;
    }

    public function setRound(?string $round): static
    {
        $this->round = $round;
        return $this;
    }





    
    #[ORM\ManyToOne]
    #[Assert\NotBlank]
    private ?User $user = null;






    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function __toString(): string
    {
      
        return $this->id . " - " . $this->league;
    }





}
