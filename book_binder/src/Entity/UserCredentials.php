<?php

namespace App\Entity;

use App\Repository\UserCredentialsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCredentialsRepository::class)]
class UserCredentials
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $user_ID = null;

    #[ORM\Column(length: 25)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserID(): ?int
    {
        return $this->user_ID;
    }

    public function setUserID(int $user_ID): self
    {
        $this->user_ID = $user_ID;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPassword(): ?int
    {
        return $this->password;
    }

    public function setPassword(int $password): self
    {
        $this->password = $password;

        return $this;
    }
}
