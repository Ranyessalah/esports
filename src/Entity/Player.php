<?php

namespace App\Entity;

use App\Enum\Niveau;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'player')]
class Player extends User
{
    #[Assert\NotBlank(message: 'Le pays est obligatoire')]
    #[ORM\Column(length: 100)]
    private string $pays = '';

    #[ORM\Column(type: 'boolean')]
    private bool $statut = true;

    #[Assert\NotBlank(message: 'Veuillez choisir un niveau')]
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $niveau = null;

    #[ORM\ManyToOne(inversedBy: 'joueur')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Equipe $equipe = null;

    public function getPays(): string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;
        return $this;
    }

    public function isStatut(): bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getNiveau(): ?Niveau
    {
        return $this->niveau !== null ? Niveau::from($this->niveau) : null;
    }

    public function setNiveau(?Niveau $niveau): static
    {
        $this->niveau = $niveau?->value;
        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): static
    {
        $this->equipe = $equipe;

        return $this;
    }
}
