<?php

namespace App\Controller;

use App\Entity\MeetupRequests;
use App\Entity\User;
use App\Form\MeetupRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Api\GoogleBooksApiClient;
class MeetupRequestsController extends AbstractController
{
    #[Route('/meetup/request/{userId}/{bookId}', name: 'app_meetup_requests')]
    public function createMeetupRequest(Request $request, int $userId, int $bookId, EntityManagerInterface $entityManager): Response
    {
        $meetupRequest = new MeetupRequests();

        // Get the currently logged-in user
        // $user = $this->getUser();

        // Get the currently logged-in user from the database
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);

        // Create the form
        $form = $this->createForm(MeetupRequestFormType::class, $meetupRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $meetupRequest->setHostUser($user);
            $meetupRequest->setBookID($bookId);

            $entityManager->persist($meetupRequest);
            $entityManager->flush();

            // Redirect to a success page or do other actions
            return $this->redirectToRoute('meetup_requests_list');
        }

        return $this->render('meetup_request/meetup_request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/meetup/requests/list', name: 'meetup_requests_list')]
    public function showLatestRequests(EntityManagerInterface $entityManager): Response
    {
        $meetupRequests = $entityManager->getRepository(MeetupRequests::class)->findBy([], ['created_at' => 'DESC'], 10);

        return $this->render('meetup_request/meetup_request_list.html.twig', [
            'meetupRequests' => $meetupRequests
        ]);
    }

    #[Route('/meetup/overview', name: 'meetup_overview')]
    public function showMeetupOverview(Security $security, EntityManagerInterface $entityManager): Response
    {
        // ============= API stuff =============
        $ApiClient = new GoogleBooksApiClient();

        // Define an array of genres to search for.
        $genres = ['fantasy', 'mystery', 'romance'];

        // Create an empty array to hold the results.
        $results = [];

        // Loop through each genre and retrieve the popular books.
        foreach ($genres as $genre) {
            $books = $ApiClient->getBooksBySubject($genre, 5);
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

        return $this->render('meetup_request/Meetup_overview.html.twig', [
            'controller_name' => 'MeetupRequestController',
            'includeProfileForm' => $includeProfileForm,
            'userEmail' => $email,
            'results' => $results
        ]);
    }

}