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

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(type: Types::STRING, enum: LanguageEnum::class, nullable: false)]
    public function getLanguages(): array
    {
        return $this->languages;
    }

    #[ORM\Column(type: Types::STRING, enum: LanguageEnum::class, nullable: false)]
    public function setLanguages(array $languages): self
    {
        $this->languages = $languages;

        return $this;
    }

    #[ORM\Column(type: Types::STRING, enum: GenreEnum::class, nullable: false)]
    public function getGenres(): array
    {
        return $this->genres;
    }

    #[ORM\Column(type: Types::STRING, enum: GenreEnum::class, nullable: false)]
    public function setGenres(array $genres): self
    {
        $this->genres = $genres;

        return $this;
    }
}
