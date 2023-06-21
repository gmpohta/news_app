<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name:"users")]
#[UniqueEntity(fields: "email", message: "Email already taken")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[Assert\Length(max: 255)]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.')] 
    #[ORM\Column(type: Types::STRING, length:255, unique: true)]
    private string $email;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING)]
    private string $password;
    
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\OneToMany(
        targetEntity: News::class, 
        mappedBy: "user", 
        cascade: ["persist", "remove"], 
        orphanRemoval: true
    )]
    private $news;

    public function getId(): int
    {
        return $this->id;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
    
    public function getEmail(): string
    {
        return (string) $this->email;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
}

