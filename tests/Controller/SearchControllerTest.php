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
use Facebook\WebDriver\WebDriverKeys;

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

    public function testHostMeetupProcess(): void
    {
        $client = static::createPantherClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Login as a user
        $crawler = $client->request('GET', '/logout');
        $this->assertStringContainsString('/login', $client->getCurrentURL());
        $form = $crawler->filter('form.form-signin')->form();
        $form['email'] = 'test@test.com';
        $form['password'] = 'password123';
        $client->submit($form);
        $this->assertStringContainsString('/', $client->getCurrentURL());

        $bookId = "l5quhLiZEiwC";
        // Visit the book page
        $crawler = $client->request('GET', "/book-page/{$bookId}");
        $this->assertStringContainsString('Superhobby', $crawler->filter('div.p-0.ps-3.col')->text());

        // Click the "Host a meetup" button with id host-up-btn-in-book
        $hostButton = $crawler->filter('#host-up-btn-in-book');
        $this->assertNotNull($hostButton);
        $hostButton->click();

        // Find the form wizard under div #host-meetup-form-in-book
        $form = $crawler->filter('#host-meetup-form-in-book form')->form();
        $this->assertNotNull($form);
        $library = $entityManager->getRepository(Library::class)->findOneBy(['library_ID' => 1]);
        // choose the first library
        $client->executeScript("document.querySelector('#host-meetup-form-in-book select[name=\"meetup_request_form[library_ID]\"]').value = '{$library->getLibraryID()}';");
        $client->executeScript("document.querySelector('#host-meetup-form-in-book input[name=\"meetup_request_form[datetime]\"]').value = '2050-01-01 00:00:00';");
        $client->executeScript("document.querySelector('#host-meetup-form-in-book input[name=\"meetup_request_form[maxNumber]\"]').value = '10';");

        // Find a button said "Confirm" and click it
        $client->executeScript("document.querySelector('#host-meetup-form-in-book #confirmButton').click();");

        // check if the page is redirected to the book page
        $this->assertStringContainsString("/book-page/{$bookId}", $client->getCurrentURL());

        // check if the meetup request is created
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@test.com']);
        $meetupRequestRepository = $entityManager->getRepository(MeetupRequests::class);
        $meetupRequest = $meetupRequestRepository->findOneBy(['book_ID' => $bookId, 'host_user' => $user]);
        $this->assertNotNull($meetupRequest);

        // Rollback the database
        $entityManager->remove($meetupRequest);
        $entityManager->flush();
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

        $bookId = "l5quhLiZEiwC";

        // Check if the review already exists
        $existingReview = $bookReviewRepository->findOneBy(['user_id' => $user, 'book_id' => $bookId]);
        if ($existingReview) {
            $entityManager->remove($existingReview);
            $entityManager->flush();
        }

        // Make a POST request to add a review
        $client->request('POST', '/add-review/'. $bookId, [
            'comment' => 'This is a test comment.',
            'rating' => 4,
        ]);

        // Check if the review has been added
        $review = $bookReviewRepository->findOneBy(['user_id' => $user, 'book_id' => $bookId]);
        $this->assertInstanceOf(BookReviews::class, $review);
        $this->assertEquals('This is a test comment.', $review->getReview());
        $this->assertEquals(4, $review->getRating());
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

    public function testBookSearchAIGetsJsonResponseWithThumbnailAndId()
    {
        $client = static::createClient();
        $client->request('GET', '/book-search/ai/harry-potter');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('thumbnail', $responseData);
        $this->assertArrayHasKey('id', $responseData);
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testSearchResult(): void
    {
        $client = static::createPantherClient();

        // Login as a user
        $crawler = $client->request('GET', '/logout');
        $this->assertStringContainsString('/login', $client->getCurrentURL());
        $form = $crawler->filter('form.form-signin')->form();
        $form['email'] = 'test@test.com';
        $form['password'] = 'password123';
        $client->submit($form);
        $this->assertStringContainsString('/', $client->getCurrentURL());

        // Search for "Harry"
        $crawler = $client->request('GET', '/');
        $searchBar = $crawler->filter('#search_bar_input');
        $this->assertNotNull($searchBar);
        $searchBar->sendKeys('Harry')->sendKeys(WebDriverKeys::ENTER);

        // Check if the page is redirected to the search result page
        $this->assertStringContainsString("/book-search/Harry", $client->getCurrentURL());

        // Update the crawler
        $crawler = $client->waitFor('#search-result-in-search');
        $this->assertStringContainsString("You searched for 'Harry'", $crawler->filter('#search-result-message')->text());

        // Find and click on the specific search result
        $resultLink = $crawler->filter('#search-result-in-search')->first();
        $this->assertNotNull($resultLink);
        $bookId = $resultLink->attr('href');
        $bookId = substr($bookId, strrpos($bookId, '/') + 1);
        $resultLink->click();

        // Check if the page is redirected to the book page
        $this->assertStringContainsString("/book-page/{$bookId}", $client->getCurrentURL());
    }

//    /**
//     * @throws NoSuchElementException
//     * @throws TimeoutException
//     */
//    public function testGenerateAIRecommendations(): void
//    {
//        $client = static::createPantherClient();
//
//        // Login as a user
//        $crawler = $client->request('GET', '/logout');
//        $this->assertStringContainsString('/login', $client->getCurrentURL());
//        $form = $crawler->filter('form.form-signin')->form();
//        $form['email'] = 'test@test.com';
//        $form['password'] = 'password123';
//        $client->submit($form);
//        $this->assertStringContainsString('/', $client->getCurrentURL());
//
//        // Update the crawler
//        $crawler = $client->waitFor('#ai-toggle-in-base');
//
//        // Open the recommendations form
//        $formToggle = $crawler->filter('#ai-toggle-in-base');
//        $this->assertNotNull($formToggle);
//        $formToggle->click();
//
//        // Update the crawler
//        $crawler = $client->waitFor('#chatForm');
//
//        // Fill in the form and submit
//        $inputText = $crawler->filter('#inputText');
//        $this->assertNotNull($inputText);
//        $inputText->sendKeys('harry');
//        $generateButton = $crawler->filter('#chatForm button[type="submit"]')->first();
//        $this->assertNotNull($generateButton);
//        $generateButton->click();
//
//        // Update the crawler
//        $crawler = $client->waitFor('#recommendations');
//
//        // Click on the recommendation
//        $recommendation = $crawler->filter('#recommendations')->first();
//        $this->assertNotNull($recommendation);
//        $recommendationLink = $recommendation->filter('a')->first();
//        $this->assertNotNull($recommendationLink);
//        $recommendationLink->click();
//
//        // Check if the page is redirected ( doesn't check to which book since ai could give different answers)
//        $this->assertStringContainsString('/book-page/', $client->getCurrentURL());
//    }

    public function testAddReviewPanther(): void
    {
        $client = static::createPantherClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Login as a user
        $crawler = $client->request('GET', '/logout');
        $this->assertStringContainsString('/login', $client->getCurrentURL());
        $form = $crawler->filter('form.form-signin')->form();
        $form['email'] = 'test@test.com';
        $form['password'] = 'password123';
        $client->submit($form);
        $this->assertStringContainsString('/', $client->getCurrentURL());

        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $bookId = "WDFWSwAACAAJ";
        $bookReviewRepository = $entityManager->getRepository(BookReviews::class);
        // Check if the review already exists and remove if it does
        $existingReview = $bookReviewRepository->findOneBy(['user_id' => $user, 'book_id' => $bookId]);
        if ($existingReview) {
            $entityManager->remove($existingReview);
            $entityManager->flush();
        }

        // Go to the book page
        $crawler = $client->request('GET', "/book-page/{$bookId}");
        $this->assertStringContainsString('Add Review', $crawler->filter('button[data-bs-toggle="modal"]')->text());

        // Click the "Add Review" button
        $addReviewButton = $crawler->filter('button[data-bs-toggle="modal"][data-bs-target="#reviewModal"]')->first();
        $this->assertNotNull($addReviewButton);
        $addReviewButton->click();

        // Check if the modal shows up
        $reviewModal = $crawler->filter('.modal.fade.show')->first();
        $this->assertNotNull($reviewModal);

        // Fill in the form and submit
        $client->executeScript("document.querySelector('#comment').value = 'This is a test comment.';");
        $client->executeScript("document.querySelector('#rating').value = 4;");
        $client->executeScript("document.querySelector('form').submit();");


        // Check if the form was submitted
        $review = $bookReviewRepository->findOneBy(['user_id' => $user, 'book_id' => $bookId]);
        $this->assertInstanceOf(BookReviews::class, $review);
        $this->assertEquals('This is a test comment.', $review->getReview());
        $this->assertEquals(4, $review->getRating());

        // Rollback the database
        $entityManager->remove($review);
        $entityManager->flush();
    }

    public function testEditReviewPanther(): void
    {
        $client = static::createPantherClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Login as a user
        $crawler = $client->request('GET', '/logout');
        $this->assertStringContainsString('/login', $client->getCurrentURL());
        $form = $crawler->filter('form.form-signin')->form();
        $form['email'] = 'test@test.com';
        $form['password'] = 'password123';
        $client->submit($form);
        $this->assertStringContainsString('/', $client->getCurrentURL());

        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $bookId = "WDFWSwAACAAJ";
        $bookReviewRepository = $entityManager->getRepository(BookReviews::class);
        // Check if the review already exists and remove if it does
        $existingReview = $bookReviewRepository->findOneBy(['user_id' => $user, 'book_id' => $bookId]);
        if ($existingReview) {
            $entityManager->remove($existingReview);
            $entityManager->flush();
        }

        // Go to the book page
        $crawler = $client->request('GET', "/book-page/{$bookId}");
        $this->assertStringContainsString('Add Review', $crawler->filter('button[data-bs-toggle="modal"]')->text());

        // Click the "Add Review" button
        $addReviewButton = $crawler->filter('button[data-bs-toggle="modal"][data-bs-target="#reviewModal"]')->first();
        $this->assertNotNull($addReviewButton);
        $addReviewButton->click();

        // Check if the modal shows up
        $reviewModal = $crawler->filter('.modal.fade.show')->first();
        $this->assertNotNull($reviewModal);

        // Fill in the form and submit
        $client->executeScript("document.querySelector('#comment').value = 'This is a test comment.';");
        $client->executeScript("document.querySelector('#rating').value = 4;");
        $client->executeScript("document.querySelector('form').submit();");


        // Check if the form was submitted
        $review = $bookReviewRepository->findOneBy(['user_id' => $user, 'book_id' => $bookId]);
        $this->assertInstanceOf(BookReviews::class, $review);
        $this->assertEquals('This is a test comment.', $review->getReview());
        $this->assertEquals(4, $review->getRating());

        // Update the review
        $updatedComment = 'This is an updated comment.';
        $updatedRating = 5;

        // Switch the button to "Edit My Review"
        $editButton = $client->getCrawler()->filter('button[data-bs-target="#reviewModal"]');
        $editButton->text('Edit My Review');

        // Click the "Edit My Review" button
        $editButton->click();

        // Check if the modal shows up
        $reviewModal = $client->getCrawler()->filter('.modal#reviewModal');
        $this->assertTrue($reviewModal->count() > 0);

        // Fill in the form with updated values and submit
        $form = $reviewModal->filter('form')->form();
        $form['comment'] = $updatedComment;
        $form['rating'] = $updatedRating;
        $client->submit($form);

        // Check if the form was submitted
        $updatedReview = $bookReviewRepository->findOneBy(['user_id' => $user, 'book_id' => $bookId]);
        $this->assertInstanceOf(BookReviews::class, $updatedReview);
        $this->assertEquals($updatedComment, $updatedReview->getReview());
        $this->assertEquals($updatedRating, $updatedReview->getRating());

        // Rollback the database
        $entityManager->remove($updatedReview);
        $entityManager->flush();
    }

    public function testReviewProfileRedirect(): void
    {
        $client = static::createPantherClient();

        // Login as a user
        $crawler = $client->request('GET', '/logout');
        $this->assertStringContainsString('/login', $client->getCurrentURL());
        $form = $crawler->filter('form.form-signin')->form();
        $form['email'] = 'test@test.com';
        $form['password'] = 'password123';
        $client->submit($form);
        $this->assertStringContainsString('/', $client->getCurrentURL());

        $bookId = "WDFWSwAACAAJ";

        // Go to the book page
        $client->request('GET', "/book-page/{$bookId}");
        $this->assertStringContainsString('Harry Potter', $client->getPageSource());

        // Click on the "Tommy" link
        $tommyLink = $client->getCrawler()->filter('a[href="/profile/Tommy"]');
        $this->assertNotNull($tommyLink);
        $tommyLink->click();

        // Check if the page is redirected to the profile page
        $this->assertStringContainsString('/profile/Tommy', $client->getCurrentURL());
    }
}