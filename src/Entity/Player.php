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
    private ?string $pays = null;

    #[ORM\Column(type: 'boolean')]
    private bool $statut = true;

    #[Assert\NotNull(message: 'Veuillez choisir un niveau')]
    #[ORM\Column(enumType: Niveau::class)]
    private ?Niveau $niveau = null;

    public function getPays(): ?string
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
        return $this->niveau;
    }

    public function setNiveau(Niveau $niveau): static
    {
        $this->niveau = $niveau;
        return $this;
    }
}
