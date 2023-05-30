<?php

namespace App\Message;

use App\Entity\Book;

class AddBookToDatabase
{
    private Book $bookData;

    public function __construct(Book $bookData)
    {
        $this->bookData = $bookData;
    }

    public function getBookData(): Book
    {
        return $this->bookData;
    }
}