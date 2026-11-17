<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(name: 'full_name', length: 100)]
    private ?string $fullName = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(name: 'role', length: 20)]
    private string $role = 'staff'; // admin, owner, staff

    #[ORM\Column(name: 'status', length: 20)]
    private string $status = 'active'; // active, inactive

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->role = 'staff';
        $this->status = 'active';
    }

    public function getId(): ?int
    {
        return $this->id ? (int)$this->id : null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = strtolower(trim($username));
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = strtolower(trim($email));
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = trim($fullName);
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = strtolower(trim($role));
        return $this;
    }

    public function getRoles(): array
    {
        $role = strtolower($this->role);
        if ($role === 'admin') {
            return ['ROLE_ADMIN', 'ROLE_OWNER', 'ROLE_USER'];
        }
        if ($role === 'owner') {
            return ['ROLE_OWNER', 'ROLE_USER'];
        }
        return ['ROLE_STAFF', 'ROLE_USER'];
    }

    public function setRoles(array $roles): static
    {
        if (in_array('ROLE_ADMIN', $roles, true)) {
            $this->role = 'admin';
        } elseif (in_array('ROLE_OWNER', $roles, true)) {
            $this->role = 'owner';
        } else {
            $this->role = 'staff';
        }
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function setIsActive(bool $isActive): static
    {
        $this->status = $isActive ? 'active' : 'inactive';
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getRoleLabel(): string
    {
        return match (strtolower($this->role)) {
            'admin' => 'Admin',
            'owner' => 'Owner',
            default => 'Staff',
        };
    }

    public function eraseCredentials(): void
    {
    }
}
