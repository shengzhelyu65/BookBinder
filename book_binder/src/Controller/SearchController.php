<?php

namespace App\Controller;

use App\Entity\MeetupRequests;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Api\GoogleBooksApiClient;
use App\Entity\MeetupRequestList;
use App\Form\MeetupRequestFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;


/*
 * This controller meant for the development of the
 * GoogleBooksApiClient, it shows examples on how to
 * use the class.
 */
class SearchController extends AbstractController
{
    #[Route('/bookSearch/{query}', name: 'bookSearch')]
    public function index($query): Response
    {
        $ApiClient = new GoogleBooksApiClient();

        $results = $ApiClient->searchBooksByTitle($query, 40);

        // Pass the results array to the Twig template.
        return $this->render('book_binder/bookSearch.html.twig', [
            'controller_name' => 'BookBinderController',
            'results' => $results,
            'query' => $query
        ]);
    }

    /**
     * @throws \Google_Exception
     */
    #[Route('/bookPage/{id}', name: 'bookPage')]
    public function clickBook($id, Security $security, EntityManagerInterface $entityManager): Response
    {
        $ApiClient = new GoogleBooksApiClient();
        $book = $ApiClient->getBookById($id);

        $thumbnailUrl = $book->getVolumeInfo()->getImageLinks()->getThumbnail();
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
        $meetupRequests = $entityManager->getRepository(MeetupRequests::class)->findBy(['book_ID' => $id], ['datetime' => 'DESC'], 10);
        // Fetch the books based on book IDs in meetupRequests


        if ($book) {
            return $this->render('book_binder/bookPage.html.twig', [
                'book' => $book,
                'thumbnailUrl' => $thumbnailUrl,
                'meetupRequests' => $meetupRequests
            ]);
        } else {
            return $this->render('book_binder/bookPage.html.twig', [
                'error' => 'No books found',
            ]);
        }
    }
}
