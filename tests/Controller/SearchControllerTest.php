<?php

namespace App\Tests\Controller;

use App\Entity\Book;
use App\Entity\BookReviews;
use App\Entity\Library;
use App\Entity\MeetupRequestList;
use App\Entity\MeetupRequests;
use App\Entity\User;
use App\Entity\UserReadingList;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\PantherTestCase;

class SearchControllerTest extends PantherTestCase
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
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
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

    public function testUpdateReview()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Simulate a logged-in user
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'user10@example.com']);
        $client->loginUser($user);

        // Find an existing review for a book
        $existingReview = $entityManager->getRepository(BookReviews::class)->findOneBy(['user_id' => $user]);
        $bookId = $existingReview->getBookId();

        // Make a POST request to update the review
        $client->request('POST', '/update-review/'. $bookId, [
            'comment' => 'Updated review comment',
            'rating' => 4,
        ]);

        // Refresh the review entity from the database
        $entityManager->refresh($existingReview);

        // Assert that the review has been updated
        $this->assertEquals('Updated review comment', $existingReview->getReview());
        $this->assertEquals(4, $existingReview->getRating());
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

    public function testToRead(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $userReadingListRepository = $entityManager->getRepository(UserReadingList::class);
        $bookRepository = $entityManager->getRepository(Book::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // Make a request to the book page and simulate adding the book to the reading list
        $id = 'l5quhLiZEiwC';

        // Visit the page
        $client->request('GET', "/book-page/{$id}");
        $bookId = $bookRepository->findOneBy(['google_books_id' => $id])->getId();

        // Find the button that opens the modal
        $modalOpenButton = $client->getCrawler()->filter('button.dropdown-item');
        $this->assertCount(3, $modalOpenButton); // Ensure the button is found

        // Check if the book is already in the reading list
        $userReadingList = $userReadingListRepository->findOneBy(['user' => $user]);
        if ($userReadingList) {
            $currentlyReading = $userReadingList->getCurrentlyReading();
            if (in_array($bookId, $currentlyReading)) {
                $index = array_search($bookId, $currentlyReading);
                unset($currentlyReading[$index]);
                $userReadingList->setCurrentlyReading($currentlyReading);
            }

            $toRead = $userReadingList->getWantToRead();
            if (in_array($bookId, $toRead)) {
                $index = array_search($bookId, $toRead);
                unset($toRead[$index]);
                $userReadingList->setWantToRead($toRead);
            }

            $read = $userReadingList->getHaveRead();
            if (in_array($bookId, $read)) {
                $index = array_search($bookId, $read);
                unset($read[$index]);
                $userReadingList->setHaveRead($read);
            }

            $entityManager->flush();
        }

        // Get the selection value for the AJAX request
        $selection = 'To Read';

        // Send an AJAX request to add the book to the reading list
        $client->request('POST', '/handle-dropdown-selection', [
            'selection' => $selection,
            'book_id' =>  $bookId,
        ]);

        // Verify the response
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Check if the book is in the reading list
        $userReadingList = $userReadingListRepository->findOneBy(['user' => $user]);
        $this->assertInstanceOf(UserReadingList::class, $userReadingList);
        $toRead = $userReadingList->getWantToRead();
        $index = array_search($bookId, $toRead);
        $this->assertGreaterThan(-1, $index);

        // Rollback the database changes
        unset($toRead[$index]);
        $userReadingList->setCurrentlyReading($toRead);
        $entityManager->flush();
    }

    public function testCurrentReading(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $userReadingListRepository = $entityManager->getRepository(UserReadingList::class);
        $bookRepository = $entityManager->getRepository(Book::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // Make a request to the book page and simulate adding the book to the reading list
        $id = 'l5quhLiZEiwC';

        // Visit the page
        $client->request('GET', "/book-page/{$id}");
        $bookId = $bookRepository->findOneBy(['google_books_id' => $id])->getId();
        $selection = 'Currently Reading';

        // Send an AJAX request to add the book to the reading list
        $client->request('POST', '/handle-dropdown-selection', [
            'selection' => $selection,
            'book_id' => $bookId,
        ]);

        // Verify the response
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Check if the book is in the reading list
        $userReadingList = $userReadingListRepository->findOneBy(['user' => $user]);
        $this->assertInstanceOf(UserReadingList::class, $userReadingList);
        $currentlyReading = $userReadingList->getCurrentlyReading();
        $index = array_search($bookId, $currentlyReading);
        $this->assertGreaterThan(-1, $index);

        // Rollback the database changes
        unset($currentlyReading[$index]);
        $userReadingList->setCurrentlyReading($currentlyReading);
        $entityManager->flush();
    }

    public function testHaveRead(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $userReadingListRepository = $entityManager->getRepository(UserReadingList::class);
        $bookRepository = $entityManager->getRepository(Book::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // Make a request to the book page and simulate adding the book to the reading list
        $id = 'l5quhLiZEiwC';

        // Visit the page
        $client->request('GET', "/book-page/{$id}");
        $bookId = $bookRepository->findOneBy(['google_books_id' => $id])->getId();
        $selection = 'Have Read';

        // Send an AJAX request to add the book to the reading list
        $client->request('POST', '/handle-dropdown-selection', [
            'selection' => $selection,
            'book_id' => $bookId,
        ]);

        // Verify the response
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Check if the book is in the reading list
        $userReadingList = $userReadingListRepository->findOneBy(['user' => $user]);
        $this->assertInstanceOf(UserReadingList::class, $userReadingList);
        $haveRead = $userReadingList->getHaveRead();
        $index = array_search($bookId, $haveRead);
        $this->assertGreaterThan(-1, $index);

        // Rollback the database changes
        unset($haveRead[$index]);
        $userReadingList->setCurrentlyReading($haveRead);
        $entityManager->flush();
    }

    public function testBookSuggestion()
    {
        $client = static::createClient();

        // Send a request to the book suggestion endpoint
        $input = 'prince';
        $client->request('GET', "/book-suggestion/{$input}");

        // Verify the response
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        // Verify the response content
        $content = $response->getContent();
        $this->assertJson($content);

        // Verify the response data type
        $data = json_decode($content, true);
        $this->assertIsArray($data);

        // Assert the expected structure of each book suggestion
        foreach ($data as $bookSuggestion) {
            $this->assertArrayHasKey('id', $bookSuggestion);
            $this->assertGreaterThan(0, $bookSuggestion['id']);
            $this->assertArrayHasKey('title', $bookSuggestion);
            $this->assertIsString($bookSuggestion['title']);
        }
    }
}