<?php

namespace App\Entity;

use App\Api\GoogleBooksApiClient;
use App\Repository\BookReviewsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Google\Service\Books\VolumeVolumeInfo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookReviewsRepository::class)]
class BookReviews
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $review_ID = null;

    #[ORM\Column]
    private ?string $book_ID = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user_ID = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(min: 1, max: 5)]
    private ?int $rating = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $tags = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $review = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    public function getReviewID(): ?int
    {
        return $this->review_ID;
    }

    public function getBookID(): ?string
    {
        return $this->book_ID;
    }

    public function setBookID(string$book_ID): self
    {
        $this->book_ID = $book_ID;

        return $this;
    }

    public function getUserID(): ?User
    {
        return $this->user_ID;
    }

    public function setUserID(User $user_ID): self
    {
        $this->user_ID = $user_ID;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getReview(): ?string
    {
        return $this->review;
    }

    public function setReview(string $review): self
    {
        $this->review = $review;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getTitleOfBook(): string {
        $ApiClient = new GoogleBooksApiClient();

        $book = $ApiClient->getBookById($this->book_ID);

        return $book->getVolumeInfo()->getTitle();
    }
}
