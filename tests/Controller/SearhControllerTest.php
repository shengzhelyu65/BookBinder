<?php

namespace Controller;

use App\Entity\Book;
use App\Entity\BookReviews;
use App\Entity\Library;
use App\Entity\MeetupRequestList;
use App\Entity\MeetupRequests;
use App\Entity\User;
use Symfony\Component\Panther\PantherTestCase;

class SearhControllerTest extends PantherTestCase
{
    public function testSearch(): void
    {
        //test book-search
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $bookRepository = $entityManager->getRepository(Book::class);

        // Find a user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // Visit the page
        $query = "prince";

        // Delete the book if it exists in the database
        $bookHamlet = $bookRepository->findOneBy(['title' => 'Hamlet']);
        if ($bookHamlet) {
            $entityManager->remove($bookHamlet);
            $entityManager->flush();
        }

        // Search for a book
        $crawler = $client->request('GET', "/book-search/{$query}");

        // Check if the page is loaded successfully
        $this->assertStringContainsString('Hamlet', $crawler->filter('b')->text());
    }

    public function testClickBook(): void
    {
        // tests book-page
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $bookRepository = $entityManager->getRepository(Book::class);

        // Find a user
        $user = $userRepository->findOneBy(['email' => 'shengzhe.lyu@gmail.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // Visit the page
        $id = "l5quhLiZEiwC";

        // Delete the book if it exists in the database
        $book = $bookRepository->findOneBy(['google_books_id' => $id]);
        if ($book) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        $crawler = $client->request('GET', "/book-page/{$id}");

        print_r($crawler->filter('div.p-0.ps-3.col')->text());
        // Check if the page is loaded successfully
        $this->assertStringContainsString('Superhobby', $crawler->filter('div.p-0.ps-3.col')->text());
    }

    public function testMultipleSearch(): void
    {
        //test if book-search returns multiple results
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Find a user
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // Visit the page
        $query = "prince";
        $crawler = $client->request('GET', "/book-search/{$query}");

        // Check if the page is loaded successfully
        $this->assertStringContainsString('Hamlet', $crawler->filter('b')->text());
        $cardElements = $crawler->filter('.card');
        $this->assertGreaterThan(5, $cardElements->count());
    }

    public function testAddReview(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $bookReviewRepository = $entityManager->getRepository(BookReviews::class);

        // Find a user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // Visit the page
        $id = "l5quhLiZEiwC";
        $crawler = $client->request('GET', "/book-page/{$id}");

        // Find the button that opens the modal
        $modalOpenButton = $crawler->filter('button[data-bs-target="#reviewModal"]');
        $this->assertCount(1, $modalOpenButton); // Ensure the button is found

        // Get the form within the modal
        $modal = $crawler->filter('#reviewModal');
        $form = $modal->filter('form')->form();

        // Test the form
        $comment = 'This is a test comment.';
        $rating = 4;
        $form['comment'] = $comment;
        $form['rating'] = $rating;
        $client->submit($form);

        // Check if there is a data record in the database
        $bookReview = $bookReviewRepository->findOneBy(['book_id' => $id, 'user_id' => $user]);
        $this->assertInstanceOf(BookReviews::class, $bookReview);

        // Check if the form submission redirects to the correct page
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect("/book-page/{$id}"));

        // Check if review is present
        $this->assertStringContainsString($comment, $crawler->filter('p.card-text.text-truncate-3')->text());
    }

    public function testJoinMeetup(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $meetupRepository = $entityManager->getRepository(MeetupRequests::class);
        $meetupRequestListRepository = $entityManager->getRepository(MeetupRequestList::class);
        $libraryRepository = $entityManager->getRepository(Library::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);
        $user9 = $userRepository->findOneBy(['email' => 'user9@example.com']);
        $this->assertInstanceOf(User::class, $user9);

        // Specify the book ID
        $bookId = 'l5quhLiZEiwC';

        // Check if the user9 already has a meetup
        $meetup = $meetupRepository->findOneBy(['book_ID' => $bookId, 'host_user' => $user9]);
        if ($meetup) {
            // Delete the meetup request list
            $meetupRequestList = $meetupRequestListRepository->findOneBy(['meetup_ID' => $meetup, 'user_ID' => $user]);
            if ($meetupRequestList) {
                $entityManager->remove($meetupRequestList);
                $entityManager->flush();
            }

            $entityManager->remove($meetup);
            $entityManager->flush();
        }

        // Create a meetup for this book
        $meetup = new MeetupRequests();
        $meetup->setBookID($bookId);
        $library = $libraryRepository->findOneBy(['library_ID' => '1']);
        $meetup->setLibraryID($library);
        $meetup->setDatetime(new \DateTime('now'));
        $meetup->setHostUser($user9);
        $meetup->setMaxNumber(5);
        $entityManager->persist($meetup);
        $entityManager->flush();

        // Get the meetup request list ID
        $meetupId = $meetup->getMeetupID();

        // Join the meetup
        $client->request('GET', "/book-page/requests/list/join/{$bookId}/{$meetupId}");

        // Check if the user is redirected to the overview page
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect("/book-page/{$bookId}"));

        // Check if there is a new entry in the meetup request list
        $meetupRequestList = $meetupRequestListRepository->findOneBy(['meetup_ID' => $meetup, 'user_ID' => $user]);
        $this->assertInstanceOf(MeetupRequestList::class, $meetupRequestList);
    }

    public function testHandleDropdownSelection(): void
    {

    }
}