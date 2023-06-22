<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name:"news")]
#[UniqueEntity(fields: "name", message: "The news with same name already exist")]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    #[Assert\NotBlank]
    #[ORM\Column(length: 255, type: Types::STRING)]
    private string $name;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private $body;

    #[ORM\ManyToOne(
        targetEntity: User::class, 
        inversedBy: "news", 
        cascade: ["persist"]
    )]
    #[ORM\JoinColumn(
        referencedColumnName: "id", 
        onDelete: "CASCADE"
    )]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}