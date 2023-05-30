<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\BookReviews;
use App\Entity\Book;
use App\Entity\UserPersonalInfo;
use App\Entity\UserReadingInterest;

use App\Message\AddBookToDatabase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Api\GoogleBooksApiClient;

class ProfileController extends AbstractController  {
    #[Route("/profile", name: 'profile')]
    public function myProfile(EntityManagerInterface $entityManager): Response
    {
        $reviews = $entityManager->getRepository(BookReviews::class)->findByUser($this->getUser()->getId());

        return $this->render('profile/profile.html.twig', [
            'controller_name' => 'ProfileController',
            'user' => $this->getUser(),
            'reviews' => $reviews
        ]);
    }

    #[Route('/profile/{username}', name: 'profile_other')]
    public function userProfile($username, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(UserPersonalInfo::class)->findOneBy(['nickname' => $username])->getUser();
        $user_reading_list = $user->getUserReadingList();
        $currently_reading = $user_reading_list->getCurrentlyReading();
        $want_to_read = $user_reading_list->getWantToRead();
        $have_read = $user_reading_list->getHaveRead();

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

        $reviews = $entityManager->getRepository(BookReviews::class)->findByUser($user->getId());

        return $this->render('profile/profile_user.html.twig', [
            'controller_name' => 'ProfileController',
            'user' => $user,
            'reviews' => $reviews,
            'currently_reading' => $currently_reading_books,
            'want_to_read' => $want_to_read_books,
            'have_read' => $have_read_books
        ]);
    }
}