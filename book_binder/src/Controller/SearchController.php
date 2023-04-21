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
    #[Route('/search', name: 'search')]
    public function index(): Response
    {
        //
        $ApiClient = new GoogleBooksApiClient();
        $books = $ApiClient->getPopularBooks("fantasy");

        if ($books) {
            echo "<br>";
            echo "<img src=".$books[0]->imageUrl.">";
            echo "<br>";
            echo $books[0]->description;
            echo "<br>";
            echo $books[0]->language;
            echo "<br>";
            echo $books[0]->title;
            echo "<br>";
            echo $books[0]->author;
            echo "<br>";
            echo $books[0]->ratingsCount;
        } else {
            echo "No books found";
        }

        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
        ]);
    }
}
