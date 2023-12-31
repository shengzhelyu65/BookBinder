<?php

namespace App\Entity;

use App\Repository\UserReadingInterestRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Enum\LanguageEnum;
use App\Enum\GenreEnum;

#[ORM\Entity(repositoryClass: UserReadingInterestRepository::class)]
class UserReadingInterest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::JSON)]
    private array $languages = [];

    #[ORM\Column]
    private array $genres = [];

    #[ORM\OneToOne(inversedBy: 'userReadingInterest', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(type: Types::STRING, nullable: false, enumType: LanguageEnum::class)]
    public function getLanguages(): array
    {
        return $this->languages;
    }

    #[ORM\Column(type: Types::STRING, nullable: false, enumType: LanguageEnum::class)]
    public function setLanguages(array $languages): self
    {
        $this->languages = $languages;

        return $this;
    }

    #[ORM\Column(type: Types::STRING, nullable: false, enumType: GenreEnum::class)]
    public function getGenres(): array
    {
        return $this->genres;
    }

    #[ORM\Column(type: Types::STRING, nullable: false, enumType: GenreEnum::class)]
    public function setGenres(array $genres): self
    {
        $this->genres = $genres;

        return $this;
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
}
