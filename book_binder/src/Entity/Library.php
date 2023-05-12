<?php

namespace App\Entity;

use App\Repository\LibraryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LibraryRepository::class)]
class Library
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $library_ID = null;

    #[ORM\Column(length: 50)]
    private ?string $library_name = null;

    #[ORM\Column]
    private ?int $zip_code = null;

    #[ORM\Column(length: 25)]
    private ?string $city = null;

    #[ORM\Column]
    private ?int $house_number = null;

    #[ORM\Column(length: 25)]
    private ?string $number = null;

    #[ORM\Column(length: 255)]
    private ?string $website = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    public function getLibraryID(): ?int
    {
        return $this->library_ID;
    }

    public function getLibraryName(): ?string
    {
        return $this->library_name;
    }

    public function setLibraryName(string $library_name): self
    {
        $this->library_name = $library_name;

        return $this;
    }

    public function getZipCode(): ?int
    {
        return $this->zip_code;
    }

    public function setZipCode(int $zip_code): self
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getHouseNumber(): ?int
    {
        return $this->house_number;
    }

    public function setHouseNumber(int $house_number): self
    {
        $this->house_number = $house_number;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
