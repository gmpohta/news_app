<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name:"users")]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[Assert\Length(max: 255)]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.')] 
    #[ORM\Column(type: Types::STRING, length:255)]
    private string $email;

    #[Assert\NotBlank()]
    #[Assert\Length(
        min: 6,
        max: 255,
        minMessage: "password.min_length",
        maxMessage: "password.max_length",
    )]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $password;
    
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
        return $this->email;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
}

