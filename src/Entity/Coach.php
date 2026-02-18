<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'coach')]
class Coach extends User
{
    #[Assert\NotBlank(message: 'La spécialité est obligatoire')]
    #[ORM\Column(length: 100)]
    private ?string $specialite = null;

    #[ORM\Column(type: 'boolean')]
    private bool $disponibilite = false;

    #[Assert\NotBlank(message: 'Le pays est obligatoire')]
    #[ORM\Column(length: 100)]
    private ?string $pays = null;

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): static
    {
        $this->specialite = $specialite;
        return $this;
    }

    public function getDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): static
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;
        return $this;
    }
}
