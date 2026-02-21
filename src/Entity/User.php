<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'user' => User::class,
    'coach' => Coach::class,
    'player' => Player::class,
])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    protected ?int $id = null;

    #[Assert\NotBlank(message: 'Email obligatoire')]
    #[Assert\Email(message: 'Email invalide')]
    #[Assert\Length(
        max: 180,
        maxMessage: "L'email ne peut pas dépasser {{ limit }} caractères"
    )]
    #[ORM\Column(length: 180)]
    protected ?string $email = null;

    #[ORM\Column]
    protected array $roles = [];

    #[ORM\Column]
    protected ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $googleId = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $isBlocked = false;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $profileImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $totpSecret = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $isTotpEnabled = false;

    // ❌ NE PAS mettre NotBlank ici car formulaire géré par mapped=false
    private ?string $plainPassword = null;

    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return (string) $this->email; }
    public function getRoles(): array { $roles = $this->roles; $roles[] = 'ROLE_USER'; return array_unique($roles); }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getGoogleId(): ?string { return $this->googleId; }
    public function setGoogleId(?string $googleId): static { $this->googleId = $googleId; return $this; }

    public function isBlocked(): bool { return $this->isBlocked; }
    public function setIsBlocked(bool $isBlocked): static { $this->isBlocked = $isBlocked; return $this; }

    public function getProfileImage(): ?string { return $this->profileImage; }
    public function setProfileImage(?string $profileImage): static { $this->profileImage = $profileImage; return $this; }

    public function getTotpSecret(): ?string { return $this->totpSecret; }
    public function setTotpSecret(?string $totpSecret): static { $this->totpSecret = $totpSecret; return $this; }

    public function isTotpEnabled(): bool { return $this->isTotpEnabled; }
    public function setIsTotpEnabled(bool $isTotpEnabled): static { $this->isTotpEnabled = $isTotpEnabled; return $this; }

    public function getPlainPassword(): ?string { return $this->plainPassword; }
    public function setPlainPassword(?string $plainPassword): static { $this->plainPassword = $plainPassword; return $this; }

    public function eraseCredentials(): void { $this->plainPassword = null; }
}
