<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'équipe est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères"
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $logo = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le jeu est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le jeu doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le jeu ne doit pas dépasser {{ limit }} caractères"
    )]
    private ?string $game = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire")]
    #[Assert\Length(
        min: 6,
        max: 50,
        minMessage: "La catégorie doit contenir au moins {{ limit }} caractères",
        maxMessage: "La catégorie ne doit pas dépasser {{ limit }} caractères"
    )]
    private ?string $categorie = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le coach est obligatoire")]
    private ?User $coach = null;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[Assert\Count(
        min: 1,
        minMessage: "Au moins un joueur doit être sélectionné"
    )]
    private Collection $joueurs;

    public function __construct()
    {
        $this->joueurs = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getLogo(): ?string { return $this->logo; }
    public function setLogo(string $logo): static { $this->logo = $logo; return $this; }
    public function getGame(): ?string { return $this->game; }
    public function setGame(string $game): static { $this->game = $game; return $this; }
    public function getCategorie(): ?string { return $this->categorie; }
    public function setCategorie(string $categorie): static { $this->categorie = $categorie; return $this; }
    public function getCoach(): ?User { return $this->coach; }
    public function setCoach(?User $coach): static { $this->coach = $coach; return $this; }
    public function getJoueurs(): Collection { return $this->joueurs; }

public function getPlayers(): Collection
{
    return $this->joueurs->filter(
        fn ($user) => $user instanceof Player
    );
}

    
  

    public function addJoueur(User $joueur): static
    {
        if (!$this->joueurs->contains($joueur)) {
            $this->joueurs->add($joueur);
        }

        return $this;
    }

    public function removeJoueur(User $joueur): static
    {
        $this->joueurs->removeElement($joueur);

        return $this;
    }
}
    
