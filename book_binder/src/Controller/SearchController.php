<?php

namespace App\Controller;

use App\Entity\MeetupList;
use App\Entity\MeetupRequests;
use App\Entity\Book;
use App\Entity\User;
use App\Message\AddBookToDatabase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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
    #[Route('/book-search/{query}', name: 'book-search')]
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
    public function clickBook($id, Security $security, EntityManagerInterface $entityManager, MessageBusInterface $messageBus): Response
    {
        // ============= API stuff =============
        // Check if book in cache
        $book = $entityManager->getRepository(Book::class)->findOneBy(['google_books_id' => $id]);
        if ($book === null) { // If no book in cache, add it
            $ApiClient = new GoogleBooksApiClient();
            $bookData = $ApiClient->getBookById($id);

            $newBook = new Book();
            $newBook->setGoogleBooksId($bookData['id']);

            if (isset($bookData['volumeInfo']['title'])) {
                $newBook->setTitle($bookData['volumeInfo']['title']);
            } else {
                $newBook->setTitle("");
            }

            if (isset($bookData['volumeInfo']['description'])) {
                Continuation:
                $newBook->setDescription($bookData['volumeInfo']['description']);
            } else {
                $newBook->setDescription("");
            }

            if (isset($bookData['volumeInfo']['imageLinks']['thumbnail'])) {
                $newBook->setThumbnail($bookData['volumeInfo']['imageLinks']['thumbnail']);
            }
            else {
                $newBook->setThumbnail("");
            }

            if (isset($bookData['volumeInfo']['averageRating'])) {
                $newBook->setRating($bookData['volumeInfo']['averageRating']);
            } else {
                $newBook->setRating(0);
            }

            if (isset($bookData['volumeInfo']['ratingsCount'])) {
                $newBook->setReviewCount($bookData['volumeInfo']['ratingsCount']);
            } else {
                $newBook->setReviewCount(0);
            }

            if (isset($bookData['volumeInfo']['authors'][0])) {
                $newBook->setAuthor($bookData['volumeInfo']['authors'][0]);
            } else {
                $newBook->setAuthor("");
            }

            if (isset($bookData['volumeInfo']['pageCount'])) {
                $newBook->setPages($bookData['volumeInfo']['pageCount']);
            } else {
                $newBook->setPages(0);
            }

            if (isset($bookData['volumeInfo']['publishedDate'])) {
                $newBook->setPublishedDate(new \DateTime($bookData['volumeInfo']['publishedDate']));
            }
            else {
                $newBook->setPublishedDate(new \DateTime());
            }

            if (isset($bookData['volumeInfo']['categories'])) {
                $newBook->setCategory($bookData['volumeInfo']['categories'][0]);
            }
            else {
                $newBook->setCategory("");
            }

            // Dispatch a new AddBookToDatabase message
            $messageBus->dispatch(new AddBookToDatabase($newBook));

            $book = $newBook;
            dump($book);
            dump($bookData);

        }

        // ============= Meetup stuff =============
        // current user for meetups
        $this->security = $security;
        $user = $this->security->getUser();

        // Get current user entity object from the database using repository method by email
        $email = $user->getEmail();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $userId = $user->getId();

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
     * @Route("/book-suggestion/{input}", name="book_suggestion", requirements={"input"=".*"})
     */
    #[Route("/book-suggestion/{input}", name: 'book_suggestion')]
    public function book_suggestion($input, EntityManagerInterface $entityManager): JsonResponse
    {
        $books = $entityManager->getRepository(Book::class)->createQueryBuilder('b')
            ->where('b.title LIKE :title')
            ->setParameter('title', '%' . $input . '%')
            ->setMaxResults(4)
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
