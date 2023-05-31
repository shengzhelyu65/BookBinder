<?php

namespace Controller;

use App\Entity\User;
use App\Entity\BookReviews;
use App\Entity\Book;
use App\Entity\UserReadingInterest;
use App\Message\AddBookToDatabase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Api\GoogleBooksApiClient;

class BookBinderController extends AbstractController
{
    #[Route("/home", name: 'app_home')]
    #[Route("/", name: 'app_home')]
    public function home(EntityManagerInterface $entityManager, MessageBusInterface $messageBus): Response
    {
        // ============= API stuff =============
        $ApiClient = new GoogleBooksApiClient();

        $user = $this->getUser();

        // Define an array of genres to search for.
        /** @var \App\Entity\User $user **/
        $genres = $user->getUserReadingInterest()->getGenres();

        array_push($genres, 'popular', 'classic');

        // Create an empty array to hold the results.
        $results = [];

        // ==================== First query the database to see if we have any books for the user's genres.
        // ==================== If no books were found in the database, query the API to retrieve them.
        // Loop through each genre and retrieve the popular books.
        foreach ($genres as $genre) {
            $books = $entityManager->getRepository(Book::class)->findBy(['category' => $genre], limit: 40);
            $results[$genre] = $books;
            $cachedCount = count($books);

            if ($cachedCount < 40) {
                $books = $ApiClient->getBooksBySubject($genre, 40 - $cachedCount);

                // Create a new array to store the Book objects
                $bookObjects = [];

                // for each book in the results array, create a new Book object and add it to the database.

                foreach ($books as $bookData) {
                    $existingBook = $entityManager->getRepository(Book::class)->findOneBy(['google_books_id' => $bookData['id']]);

                    if ($existingBook === null) {
                        // Complete the book object with the data from the API
                        $newBook = new Book();
                        $newBook->setGoogleBooksId($bookData['id']);

                        if (isset($bookData['volumeInfo']['title'])) {
                            $newBook->setTitle($bookData['volumeInfo']['title']);
                        } else {
                            $newBook->setTitle("");
                        }

                        if (isset($bookData['volumeInfo']['description'])) {
                            Continuation:
                            $newBook->setDescription($bookData['volumeInfo']['description']);
                        } else {
                            $newBook->setDescription("");
                        }

                        if (isset($bookData['volumeInfo']['imageLinks']['thumbnail'])) {
                            $newBook->setThumbnail($bookData['volumeInfo']['imageLinks']['thumbnail']);
                        } else {
                            $newBook->setThumbnail("");
                        }

                        if (isset($bookData['volumeInfo']['averageRating'])) {
                            $newBook->setRating($bookData['volumeInfo']['averageRating']);
                        } else {
                            $newBook->setRating(0);
                        }

                        if (isset($bookData['volumeInfo']['ratingsCount'])) {
                            $newBook->setReviewCount($bookData['volumeInfo']['ratingsCount']);
                        } else {
                            $newBook->setReviewCount(0);
                        }

                        if (isset($bookData['volumeInfo']['authors'][0])) {
                            $newBook->setAuthor($bookData['volumeInfo']['authors'][0]);
                        } else {
                            $newBook->setAuthor("");
                        }

                        if (isset($bookData['volumeInfo']['pageCount'])) {
                            $newBook->setPages($bookData['volumeInfo']['pageCount']);
                        } else {
                            $newBook->setPages(0);
                        }

                        if (isset($bookData['volumeInfo']['publishedDate'])) {
                            $newBook->setPublishedDate(new \DateTime($bookData['volumeInfo']['publishedDate']));
                        } else {
                            $newBook->setPublishedDate(new \DateTime());
                        }

                        $newBook->setCategory($genre);

                        // Dispatch a new AddBookToDatabase message
                        $messageBus->dispatch(new AddBookToDatabase($newBook));

                        // Check if any non-nullable fields are missing
                        if ($newBook->getTitle() !== null && $newBook->getAuthor() !== null) {
                            // Add the Book object to the array
                            $bookObjects[] = $newBook;
                        }
                    }
                }

                // Assign the Book objects to the $results array
                if ($results[$genre] === null) {
                    $results[$genre] = $bookObjects;
                } else {
                    $results[$genre] = array_merge($results[$genre], $bookObjects);
                }
            }
        }
        // ============= Reviews

        $reviews = $entityManager->getRepository(BookReviews::class)->findLatest(5);

        // =============

        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
            'results' => $results,
            'reviews' => $reviews
        ]);
    }
}
