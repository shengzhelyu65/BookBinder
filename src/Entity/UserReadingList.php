<?php

namespace App\Entity;

use App\Repository\UserReadingListRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserReadingListRepository::class)]
class UserReadingList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'userReadingList', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private array $currently_reading = [];

    #[ORM\Column]
    private array $want_to_read = [];

    #[ORM\Column]
    private array $have_read = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCurrentlyReading(): array
    {
        return $this->currently_reading;
    }

    public function setCurrentlyReading(array $currently_reading): self
    {
        $this->currently_reading = $currently_reading;

        return $this;
    }

    public function getWantToRead(): array
    {
        return $this->want_to_read;
    }

    public function setWantToRead(array $want_to_read): self
    {
        $this->want_to_read = $want_to_read;

        return $this;
    }

    public function getHaveRead(): array
    {
        return $this->have_read;
    }

    public function setHaveRead(array $have_read): self
    {
        $this->have_read = $have_read;

        return $this;
    }
}
