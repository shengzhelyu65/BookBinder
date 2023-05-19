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
        //
        $ApiClient = new GoogleBooksApiClient();
        $books = $ApiClient->getPopularBooks("fantasy", 3);

        return $this->render('book_binder/bookSearch.html.twig', [
            'controller_name' => 'BookBinderController',
            'books' => $books,
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
