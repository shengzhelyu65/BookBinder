<?php

namespace App\Controller;

use App\Entity\MeetupList;
use App\Entity\MeetupRequestList;
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
    public function createMeetupRequest(Request $request, int $userId, String $bookId, EntityManagerInterface $entityManager): Response
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
        $meetupRequests = $entityManager->getRepository(MeetupRequests::class)->findBy([], ['datetime' => 'DESC'], 10);

        // TODO: only show the requests that the user has not joined yet
        // TODO: and that are not hosted by the user, and that are not expired
        // TODO: and that are not full, and in the user's library

        return $this->render('meetup_request/meetup_request_list.html.twig', [
            'meetupRequests' => $meetupRequests
        ]);
    }

    #[Route('/meetup/requests/list/join/{userId}/{meetupRequestId}', name: 'meetup_requests_list_join')]
    public function joinMeetupRequest(int $userId, int $meetupRequestId, EntityManagerInterface $entityManager): Response
    {
        // Get the User and MeetupRequest entities based on the provided IDs
        $user = $entityManager->getRepository(User::class)->find($userId);
        $meetupRequest = $entityManager->getRepository(MeetupRequests::class)->find($meetupRequestId);

        if ($user && $meetupRequest) {
            // Check if the user is already the host
            if ($user != $meetupRequest->getHostUser()) {
                // Check if the maximum number of participants has been reached
                $participantsCount = $entityManager->getRepository(MeetupList::class)->count(['meetup_ID' => $meetupRequest]);
                $maxParticipants = $meetupRequest->getMaxNumber();

                if ($participantsCount < $maxParticipants - 1) {
                    // Check if the user has already joined the meetup request
                    $existingRequest = $entityManager->getRepository(MeetupRequestList::class)->findOneBy(['meetup_ID' => $meetupRequest, 'user_ID' => $user]);

                    if (!$existingRequest) {
                        // Create a new MeetupRequestList entity
                        $meetupRequestList = new MeetupRequestList();
                        $meetupRequestList->setMeetupID($meetupRequest);
                        $meetupRequestList->setUserID($user);

                        // Persist the new entity
                        $entityManager->persist($meetupRequestList);
                        $entityManager->flush();
                    }
                }
            }
        }

        $meetupRequests = $entityManager->getRepository(MeetupRequests::class)->findBy([], ['datetime' => 'DESC'], 10);
        // Redirect or return a response
        return $this->render('meetup_request/meetup_request_list.html.twig', [
            'meetupRequests' => $meetupRequests
        ]);
    }

    #[Route('/meetup/requests/host/{hostId}', name: 'meetup_requests_host')]
    public function meetupRequestsHost(int $hostId, EntityManagerInterface $entityManager): Response
    {
        // $user = $this->getUser(); // Assuming you have user authentication
        $user = $entityManager->getRepository(User::class)->find($hostId);

        // Retrieve the meetups hosted by the user
        $hostedMeetups = $entityManager->getRepository(MeetupRequests::class)->findBy(['host_user' => $user]);

        // Retrieve the meetup requests for the hosted meetups
        $meetupRequests = $entityManager->getRepository(MeetupRequestList::class)->findBy(['meetup_ID' => $hostedMeetups]);

        return $this->render('meetup_request/meetup_request_host.html.twig', [
            'meetupRequests' => $meetupRequests,
        ]);
    }

    #[Route('/meetup/request/host/accept/{meetupRequestId}', name: 'meetup_request_host_accept')]
    public function acceptMeetupRequest(int $meetupRequestId, EntityManagerInterface $entityManager, Request $request): Response
    {
        $action = $request->request->get('action');

        $meetupRequest = $entityManager->getRepository(MeetupRequestList::class)->find($meetupRequestId);
        $host = $meetupRequest->getMeetupID()->getHostUser()->getId();

        if ($action === 'accept') {
            // Retrieve the meetup request details
            $meetup = $meetupRequest->getMeetupID();
            $user = $meetupRequest->getUserID();

            // Create a new MeetupList entity
            $meetupList = new MeetupList();
            $meetupList->setMeetupID($meetup);
            $meetupList->setUserID($user);

            // Persist the new entity
            $entityManager->persist($meetupList);
        }

        // Remove the meetup request from the MeetupRequestList table
        $entityManager->remove($meetupRequest);
        $entityManager->flush();

        return $this->redirectToRoute('meetup_requests_host', ['hostId' => $host]);
    }

    #[Route('/meetup/requests/upcoming/{userId}', name: 'meetup_requests_upcoming')]
    public function upcomingMeetupRequests(int $userId, EntityManagerInterface $entityManager): Response
    {
        // Get the joined meetup requests for the user
        $joinedRequests = $entityManager->getRepository(MeetupList::class)->findBy(['user_ID' => $userId]);
        $joinedMeetupIds = array_map(function ($joinedRequest) {
            return $joinedRequest->getMeetupID();
        }, $joinedRequests);

        // Get the hosted meetup requests for the user
        $hostedRequests = $entityManager->getRepository(MeetupRequests::class)->findBy(['host_user' => $userId]);

        // Combine the meetup requests into a single list
        $upcomingRequests = array_merge($joinedMeetupIds, $hostedRequests);

        // Sort the meetup requests by datetime
        usort($upcomingRequests, function ($a, $b) {
            return $a->getDatetime() <=> $b->getDatetime();
        });

        return $this->render('meetup_request/meetup_request_upcoming.html.twig', [
            'upcomingRequests' => $upcomingRequests,
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