<?php

namespace App\Controller;

use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


class ReadingListController extends AbstractController
{
    #[Route('/reading-list', name: 'reading_list')]
    public function showReadingList(EntityManagerInterface $entityManager): Response
    {

        // fetch the user's reading list
        // In turns out that adding this comment fixed the VS Code intelephense error, not sure if that's the case for
        // Intelj as well
        $user = $this->getUser();
        $user_reading_list = $user->getUserReadingList();

        // if the user has a reading list, fetch the books
        if ($user_reading_list) {
            $currently_reading = $user_reading_list->getCurrentlyReading();
            $want_to_read = $user_reading_list->getWantToRead();
            $have_read = $user_reading_list->getHaveRead();
        }

        $currently_reading_books = [];

        // For the ids in currently_reading, add the book objects to $books
        foreach ($currently_reading as $book_id) {
            $book = $entityManager->getRepository(Book::class)->findOneBy(['id' => $book_id]);
            array_push($currently_reading_books, $book);
        }

        // For the ids in want_to_read, add the book objects to $books
        $want_to_read_books = [];

        foreach ($want_to_read as $book_id) {
            $book = $entityManager->getRepository(Book::class)->findOneBy(['id' => $book_id]);
            array_push($want_to_read_books, $book);
        }

        // For the ids in have_read, add the book objects to $books
        $have_read_books = [];

        foreach ($have_read as $book_id) {
            $book = $entityManager->getRepository(Book::class)->findOneBy(['id' => $book_id]);
            array_push($have_read_books, $book);
        }

        // render the reading list page
        return $this->render('reading_list/reading_list.html.twig', [
            'currently_reading' => $currently_reading_books,
            'want_to_read' => $want_to_read_books,
            'have_read' => $have_read_books,
        ]);
    }
}
