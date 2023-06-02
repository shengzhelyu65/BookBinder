<?php

namespace App\Tests\Controller;

use App\Entity\BookReviews;
use App\Entity\User;
use App\Entity\UserReadingInterest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookBinderControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Find a user
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // test the index page
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testGenresReviews(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $userReadingInterestRepository = $entityManager->getRepository(UserReadingInterest::class);
        $bookReviewsRepository = $entityManager->getRepository(BookReviews::class);

        // Find a user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // test the index page
        $crawler = $client->request('GET', '/');

        // test the genres
        $genre = $userReadingInterestRepository->findOneBy(['user' => $user]);
        $this->assertStringContainsString($genre->getGenres()[0], $crawler->filter('h4')->text());

        // test the reviews
        // Filter all the h4 elements that contain the genre name
        $this->assertStringContainsString('Reviews', $crawler->filter('div.col-md-4.d-none.d-md-block h4')->text());
        $review = $bookReviewsRepository->findLatest(1);
        $this->assertStringContainsString($review[0]->getReview(), $crawler->filter('div.col-md-4.d-none.d-md-block div.card-body p')->text());
    }
}