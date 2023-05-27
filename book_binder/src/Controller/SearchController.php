<?php

namespace App\Controller;

use App\Entity\MeetupList;
use App\Entity\MeetupRequests;
use App\Entity\Book;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        return $this->render('book_binder/book_search.html.twig', [
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

        // current user for meetups
        $this->security = $security;
        $user = $this->security->getUser();

        // Get current user entity object from the database using repository method by email
        $email = $user->getEmail();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $userId = $user->getId();

        $thumbnailUrl = $book->getVolumeInfo()->getImageLinks()->getThumbnail();

        //$meetupRequests = $entityManager->getRepository(MeetupRequests::class)->findBy(['book_ID' => $id], ['datetime' => 'DESC'], 10);
        $meetupRequests = $entityManager->createQueryBuilder()
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
            ->andWhere('mr.book_ID = :bookId')
            ->setParameter('userId', $userId)
            ->setParameter('bookId', $id)
            ->orderBy('mr.datetime', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
        // Fetch the books based on book IDs in meetupRequests

        return $this->render('book_binder/book_page.html.twig', [
            'book' => $book,
            'thumbnailUrl' => $thumbnailUrl,
            'meetupRequests' => $meetupRequests
        ]);
    }
    #[Route('/bookPage/requests/list/join/{bookId}/{meetupRequestId}', name: 'meetup_requests_list_join_book')]
    public function joinMeetupRequest(String $bookId, int $meetupRequestId,Security $security, EntityManagerInterface $entityManager): Response
    {
        $this->security = $security;
        $user = $this->security->getUser();

        // Get current user entity object from the database using repository method by email
        $email = $user->getEmail();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        // Get the User and MeetupRequest entities based on the provided IDs

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
        return $this->redirectToRoute('bookPage', ['id' => $bookId]);
    }

    /**
     * @Route("/bookSuggestion/{input}", name="book_suggestion", requirements={"input"=".*"})
     */
    #[Route("/bookSuggestion/{input}", name: 'book_suggestion')]
    public function bookSuggestion($input, EntityManagerInterface $entityManager): JsonResponse
    {
        $books = $entityManager->getRepository(Book::class)->createQueryBuilder('b')
            ->where('b.title LIKE :title')
            ->setParameter('title', '%' . $input . '%')
            ->getQuery()
            ->getResult();

        $suggestions = [];
        foreach ($books as $book) {
            $suggestions[] = [
                'id' => $book->getId(),
                'title' => $book->getTitle(),
                // add any other book properties you need
            ];
        }

        return new JsonResponse($suggestions);
    }
}
