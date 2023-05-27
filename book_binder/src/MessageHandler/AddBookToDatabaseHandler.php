<?php
namespace App\MessageHandler;

use App\Entity\Book;
use App\Message\AddBookToDatabase;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddBookToDatabaseHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private BookRepository $bookRepository;

    public function __construct(EntityManagerInterface $entityManager, BookRepository $bookRepository)
    {
        $this->entityManager = $entityManager;
        $this->bookRepository = $bookRepository;
    }

    public function __invoke(AddBookToDatabase $addBookToDatabase): void
    {
        $bookData = $addBookToDatabase->getBookData();

        // Put the logic for creating a new Book object and persisting it to the database here.
        // ...

        $this->entityManager->persist($bookData);
        $this->entityManager->flush();
    }
}