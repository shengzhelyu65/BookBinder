<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Api\GoogleBooksApiClient;

/*
 * This controller meant for the development of the
 * GoogleBooksApiClient, it shows examples on how to
 * use the class.
 */
class SearchController extends AbstractController
{
    #[Route('/bookSearch', name: 'bookSearch')]
    public function index(): Response
    {
        $ApiClient = new GoogleBooksApiClient();

        // Define an array of genres to search for.
        $genres = ['fantasy', 'mystery', 'romance'];

        // Create an empty array to hold the results.
        $results = [];

        // Loop through each genre and retrieve the popular books.
        foreach ($genres as $genre) {
            $books = $ApiClient->getBooksBySubject($genre, 10);
            $results[$genre] = $books;
        }

        // Pass the results array to the Twig template.
        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
            'results' => $results,
        ]);
    }

    /**
     * @throws \Google_Exception
     */
    #[Route('/bookPage/{id}', name: 'bookPage')]
    public function clickBook($id): Response
    {
        $ApiClient = new GoogleBooksApiClient();
        $book = $ApiClient->getBookById($id);

        $thumbnailUrl = $book->getVolumeInfo()->getImageLinks()->getThumbnail();

        if ($book) {
            return $this->render('book_binder/bookPage.html.twig', [
                'book' => $book,
                'thumbnailUrl' => $thumbnailUrl
            ]);
        } else {
            return $this->render('book_binder/bookPage.html.twig', [
                'error' => 'No books found',
            ]);
        }
    }
}
