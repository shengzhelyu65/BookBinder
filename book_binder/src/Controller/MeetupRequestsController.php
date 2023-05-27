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
    #[Route('/meetup/request/{bookId}', name: 'app_meetup_requests')]
    public function createMeetupRequest(Request $request, String $bookId, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $meetupRequest = new MeetupRequests();

            // Get the currently logged-in user
            $user = $this->getUser();

            // Create the form
            $form = $this->createForm(MeetupRequestFormType::class, $meetupRequest);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $meetupRequest->setHostUser($user);
                $meetupRequest->setBookID($bookId);

                $entityManager->persist($meetupRequest);
                $entityManager->flush();

                // Redirect to a success page or do other actions
                return $this->redirectToRoute('meetup_overview');
            }
        }

        return $this->render('meetup_request/meetup_request.html.twig', [
            'form' => $form->createView(),
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

        // Redirect or return a response
        return $this->redirectToRoute('meetup_overview');
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

        return $this->redirectToRoute('meetup_overview');
    }

    #[Route('/meetup/overview', name: 'meetup_overview')]
    public function showMeetupOverview(Security $security, EntityManagerInterface $entityManager): Response
    {
        $ApiClient = new GoogleBooksApiClient();

        // Get the current user
        $this->security = $security;
        $user = $this->security->getUser();

        // Get current user entity object from the database using repository method by email
        $email = $user->getEmail();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $userId = $user->getId();

        // The third column
        // Retrieve the latest meetup requests
        // $meetupRequests = $entityManager->getRepository(MeetupRequests::class)->findBy([], ['datetime' => 'DESC'], 10);
        $meetupAvailables = $entityManager->createQueryBuilder()
            ->select('mr')
            ->from('App\Entity\MeetupRequests', 'mr')
            ->leftJoin('App\Entity\MeetupList', 'ml', 'WITH', 'mr.meetup_ID = ml.meetup_ID')
            ->leftJoin('App\Entity\MeetupRequestList', 'mrl', 'WITH', 'mr.meetup_ID = mrl.meetup_ID')
            ->where('mr.host_user != :userId AND NOT EXISTS (
        SELECT 1 FROM App\Entity\MeetupList subml
        WHERE subml.meetup_ID = mr.meetup_ID AND subml.user_ID = :userId
    )')
            ->andWhere('NOT EXISTS (
        SELECT 1 FROM App\Entity\MeetupRequestList submrl
        WHERE submrl.meetup_ID = mr.meetup_ID AND submrl.user_ID = :userId
    )')
            ->setParameter('userId', $userId)
            ->orderBy('mr.datetime', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
        // Fetch the books based on book IDs in meetupRequests
        $booksMeetupAvailables = [];
        foreach ($meetupAvailables as $meetupAvailable) {
            $bookId = $meetupAvailable->getBookID();
            $book = $ApiClient->getBookById($bookId);
            $booksMeetupAvailables[$bookId] = $book;
        }

        // The second column
        // Retrieve the meetups hosted by the user
        $hostedMeetups = $entityManager->getRepository(MeetupRequests::class)->findBy(['host_user' => $user]);
        $meetupRequests = $entityManager->getRepository(MeetupRequestList::class)->findBy(['meetup_ID' => $hostedMeetups]);
        $booksMeetupRequests = [];
        foreach ($meetupRequests as $meetupRequest) {
            $bookId = $meetupRequest->getMeetupID()->getBookID();
            $book = $ApiClient->getBookById($bookId);
            $booksMeetupRequests[$bookId] = $book;
        }

        // The first column
        // Get the joined meetup requests for the user
        $joinedRequests = $entityManager->getRepository(MeetupList::class)->findBy(['user_ID' => $userId]);
        $joinedMeetupIds = array_map(function ($joinedRequest) {
            return $joinedRequest->getMeetupID();
        }, $joinedRequests);
        // Combine the meetup requests into a single list
        $upcomingRequests = array_merge($joinedMeetupIds, $hostedMeetups);
        // Sort the meetup requests by datetime
        usort($upcomingRequests, function ($a, $b) {
            return $a->getDatetime() <=> $b->getDatetime();
        });
        $booksUpcomingRequests = [];
        foreach ($upcomingRequests as $upcomingRequest) {
            $bookId = $upcomingRequest->getBookID();
            $book = $ApiClient->getBookById($bookId);
            $booksUpcomingRequests[$bookId] = $book;
        }

        return $this->render('meetup_request/meetup_overview.html.twig', [
            'controller_name' => 'MeetupRequestController',
            'userEmail' => $email,
            'userId' => $userId,
            'upcomingRequests' => $upcomingRequests,
            'booksUpcomingRequests' => $booksUpcomingRequests,
            'meetupRequests' => $meetupRequests,
            'booksMeetupRequests' => $booksMeetupRequests,
            'meetupAvailabes'=>$meetupAvailables,
            'booksMeetupAvailables' => $booksMeetupAvailables,
        ]);
    }
}