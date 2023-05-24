<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use App\Api\GoogleBooksApiClient;

class BookBinderController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Security $security, EntityManagerInterface $entityManager): Response
    {
        // ============= API stuff =============
        $ApiClient = new GoogleBooksApiClient();

        // Define an array of genres to search for.
        $genres = ['fantasy', 'mystery', 'romance'];

        // Create an empty array to hold the results.
        $results = [];

        // Loop through each genre and retrieve the popular books.
        foreach ($genres as $genre) {
            $books = $ApiClient->getBooksBySubject($genre, 40);
            $results[$genre] = $books;
        }

        // =============

        $includeProfileForm = false; // Set this to true or false depending on your condition

        $this->security = $security;
        $user = $this->security->getUser();

        // print_r($user);
        // Get current user entity object from the database using repository method by email
        $email = $user->getEmail();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        // print_r($user->getUserIdentifier());

        // Check if the userPersonalInfo entity object is null
        if ($user->getUserPersonalInfo() == null) {
            // If it is null, set the includeProfileForm to false
            $includeProfileForm = true;
        }

        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
            'includeProfileForm' => $includeProfileForm,
            'userEmail' => $email,
            'results' => $results
        ]);
    }

    #[Route("/home", name: 'app_home')]
    public function home(): Response
    {
        return $this->render('book_binder/index.html.twig', [
            'controller_name' => 'BookBinderController',
        ]);
    }
}
