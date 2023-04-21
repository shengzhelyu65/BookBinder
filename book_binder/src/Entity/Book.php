<?php

namespace App\Entity;

/**
 * This class stores all the information of a Book
 */
class Book
{
    public string $id;
    public string $title;
    public string $author;
    public string $description;
    public string $imageUrl;
    public string $publishedDate;
    public array $industryIdentifiers;
    public int $pageCount;
    public string $printType;
    public float $averageRating;
    public int $ratingsCount;
    public string $maturityRating;
    public bool $allowAnonLogging;
    public string $contentVersion;
    public string $language;
    public string $previewLink;
    public string $infoLink;

    public function __construct($id, $title, $author, $description, $imageUrl, $publishedDate, $industryIdentifiers, $pageCount, $printType, $averageRating, $ratingsCount, $maturityRating, $allowAnonLogging, $contentVersion, $language, $previewLink, $infoLink)
    {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->description = $description;
        $this->imageUrl = $imageUrl;
        $this->publishedDate = $publishedDate;
        $this->industryIdentifiers = $industryIdentifiers;
        $this->pageCount = $pageCount;
        $this->printType = $printType;
        $this->averageRating = $averageRating;
        $this->ratingsCount = $ratingsCount;
        $this->maturityRating = $maturityRating;
        $this->allowAnonLogging = $allowAnonLogging;
        $this->contentVersion = $contentVersion;
        $this->language = $language;
        $this->previewLink = $previewLink;
        $this->infoLink = $infoLink;
    }
}