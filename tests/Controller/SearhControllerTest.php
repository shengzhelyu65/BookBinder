<?php

namespace Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SearhControllerTest extends WebTestCase
{
    public function testClickBook(): void
    {
        // tests book-page
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Find a user
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'shengzhe.lyu@gmail.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // Visit the page
        $id = "l5quhLiZEiwC";
        $crawler = $client->request('GET', "/book-page/{$id}");

        // Check if the page is loaded successfully
        $this->assertStringContainsString('Superhobby', $crawler->filter('h1')->text());
    }
    public function testIndex(): void
    {
        //test book-search
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Find a user
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'shengzhe.lyu@gmail.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // Visit the page
        $query = "prince";
        $crawler = $client->request('GET', "/book-search/{$query}");

        // Check if the page is loaded successfully
        $this->assertStringContainsString('Hamlet', $crawler->filter('b')->text());

    }
    public function testMultipleSearch(): void
    {
        //test if book-search returns multiple results
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Find a user
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'shengzhe.lyu@gmail.com']);
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

        // Find a user
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'shengzhe.lyu@gmail.com']);
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

        // TODO: check db for succesfull creation
        // currently only tests front-end

        // Check if the form submission redirects to the correct page
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect("/book-page/{$id}"));

        //check if review is present (will always be true since prev tests don't get deleted TODO: fix this)
        $this->assertStringContainsString('comment', $crawler->filter('p.card-text')->text());


    }

    public function testJoinMeetupRequest(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Find a user
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'thomas.goris2668@gmail.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // TODO: create a new meetuprequest db entry here
        // because test works one time, because you cant join the same one twice, you get it, you're smart
        $bookId = "l5quhLiZEiwC";
        $meetupRequestId = 12;
        $client->request('GET', "/book-page/requests/list/join/{$bookId}/{$meetupRequestId}");
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect('/meetup/overview'));
        // TODO: check db

        // TODO: delete db entry

    }
    public function testHandleDropdownSelection(): void
    {
        // TODO: test the different cases
    }

}