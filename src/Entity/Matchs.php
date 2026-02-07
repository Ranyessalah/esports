<?php

namespace App\Entity;

use App\Repository\MatchsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MatchsRepository::class)]
#[Assert\Expression(
    "this.getDateFinMatch() > this.getDateMatch()",
    message: "La date de fin doit être postérieure à la date du match"
)]
class Matchs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 30,
        minMessage: "Le statut doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le statut ne doit pas dépasser {{ limit }} caractères"
    )]
    private ?string $statut = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date du match est obligatoire")]
    #[Assert\GreaterThan(
        "today",
        message: "La date du match doit être postérieure à la date actuelle"
    )]
    private ?\DateTime $dateMatch = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de fin est obligatoire")]
    private ?\DateTime $dateFinMatch = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le score doit être positif ou zéro")]
    private ?int $scoreEquipe1 = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le score doit être positif ou zéro")]
    private ?int $scoreEquipe2 = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'équipe 1 est obligatoire")]
    private ?Equipe $equipe1 = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'équipe 2 est obligatoire")]
    private ?Equipe $equipe2 = null;

    public function getId(): ?int { return $this->id; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getDateMatch(): ?\DateTime { return $this->dateMatch; }
    public function setDateMatch(\DateTime $dateMatch): static { $this->dateMatch = $dateMatch; return $this; }
    public function getDateFinMatch(): ?\DateTime { return $this->dateFinMatch; }
    public function setDateFinMatch(\DateTime $dateFinMatch): static { $this->dateFinMatch = $dateFinMatch; return $this; }
    public function getScoreEquipe1(): ?int { return $this->scoreEquipe1; }
    public function setScoreEquipe1(?int $scoreEquipe1): static { $this->scoreEquipe1 = $scoreEquipe1; return $this; }
    public function getScoreEquipe2(): ?int { return $this->scoreEquipe2; }
    public function setScoreEquipe2(?int $scoreEquipe2): static { $this->scoreEquipe2 = $scoreEquipe2; return $this; }
    public function getEquipe1(): ?Equipe { return $this->equipe1; }
    public function setEquipe1(?Equipe $equipe1): static { $this->equipe1 = $equipe1; return $this; }
    public function getEquipe2(): ?Equipe { return $this->equipe2; }
    public function setEquipe2(?Equipe $equipe2): static { $this->equipe2 = $equipe2; return $this; }
}
