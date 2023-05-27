<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\BookReviews;
use App\Entity\Book;
use App\Entity\UserReadingInterest;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Api\GoogleBooksApiClient;

class BookBinderController extends AbstractController
{
    #[Route("/home", name: 'app_home')]
    #[Route("/", name: 'app_home')]
    public function home(EntityManagerInterface $entityManager): Response
    {
        // ============= API stuff =============
        $ApiClient = new GoogleBooksApiClient();

        $user = $this->getUser();

        // Define an array of genres to search for.
        $genres = $user->getUserReadingInterest()->getGenres();

        // Create an empty array to hold the results.
        $results = [];

        // ==================== First query the database to see if we have any books for the user's genres.


        // else

        // Loop through each genre and retrieve the popular books.
        foreach ($genres as $genre) {
            $books = $ApiClient->getBooksBySubject($genre, 40);
            $results[$genre] = $books;

            // ============= If no books were found in the cache table, the use the API to populate the cache table.

            // for each book in the results array, create a new Book object and add it to the database.
            foreach ($results as $genre => $books) {
                foreach ($books as $bookData) {
                    $existingBook = $entityManager->getRepository(Book::class)->findOneBy(['google_books_id' => $bookData['id']]);

                    if ($existingBook === null) {
                        // Complete the book object with the data from the API
                        $newBook = new Book();
                        $newBook->setGoogleBooksId($bookData['id']);

                        if (isset($bookData['volumeInfo']['title'])) {
                            $newBook->setTitle($bookData['volumeInfo']['title']);
                        }

                        if (isset($bookData['volumeInfo']['description'])) {
                            $newBook->setDescription($bookData['volumeInfo']['description']);
                        }

                        if (isset($bookData['VolumeVolumeInfoImageLinks']['thumbnail'])) {
                            $newBook->setThumbnail($bookData['volumeInfo']['thumbnail']);
                        }

                        if (isset($bookData['volumeInfo']['averageRating'])) {
                            $newBook->setRating($bookData['volumeInfo']['averageRating']);
                        }

                        if (isset($bookData['volumeInfo']['ratingsCount'])) {
                            $newBook->setReviewCount($bookData['volumeInfo']['ratingsCount']);
                        }

                        if (isset($bookData['volumeInfo']['authors'][0])) {
                            $newBook->setAuthor($bookData['volumeInfo']['authors'][0]);
                        }

                        if (isset($bookData['volumeInfo']['pageCount'])) {
                            $newBook->setPages($bookData['volumeInfo']['pageCount']);
                        }

                        if (isset($bookData['volumeInfo']['publishedDate'])) {
                            $newBook->setPublishedDate(new \DateTime($bookData['volumeInfo']['publishedDate']));
                        }

                        // maybe just set this to the genre?
                        $newBook->setCategory([$genre]);

                        // dump the book data to the screen to console
                        dump($bookData);
                        dump($newBook);

                        // Check if any non-nullable fields are missing
                        if ($newBook->getTitle() !== null && $newBook->getAuthor() !== null) {
                            $entityManager->persist($newBook);
                        }
                    }
                }
            }
            $entityManager->flush();
        }

        // ============= Reviews

        $reviews = $entityManager->getRepository(BookReviews::class)->findLatest(10);

        // =============

        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
            'results' => $results,
            'reviews' => $reviews
        ]);
    }

    #[Route("/profile", name: 'profile')]
    public function profile(): Response
    {
        return $this->render('book_binder/profile.html.twig', [
            'controller_name' => 'BookBinderController',
            'user' => $this->getUser()
        ]);
    }
}
