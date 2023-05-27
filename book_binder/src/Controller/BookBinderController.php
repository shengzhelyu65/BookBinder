<?php

namespace App\Controller;

use App\Entity\BookReviews;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
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

        // Loop through each genre and retrieve the popular books.
        foreach ($genres as $genre) {
            $books = $ApiClient->getBooksBySubject($genre, 40);
            $results[$genre] = $books;
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
