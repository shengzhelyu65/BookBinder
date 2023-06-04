<?php

namespace App\Tests\Controller;

use App\Entity\Book;
use App\Entity\BookReviews;
use App\Entity\User;
use App\Entity\UserReadingInterest;
use App\Entity\UserReadingList;
use App\Enum\GenreEnum;
use App\Enum\LanguageEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookBinderControllerTest extends WebTestCase
{
    public function testHomeRedirectsToLoginWhenUserIsNull()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertNotNull($client->getResponse()->isRedirect('/login'));
    }

    public function testHomeRedirectsToReadingInterestWhenGenresNotFound()
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // Delete the user's reading interest
        $userReadingInterestRepository = $entityManager->getRepository(UserReadingInterest::class);
        $readingInterest = $userReadingInterestRepository->findOneBy(['user' => $user]);
        $genres = $readingInterest->getGenres();
        $readingInterest->setGenres([]);
        $entityManager->flush();

        // Visit the home page
        $client->request('GET', '/');
        $this->assertNotNull($client->getResponse()->isRedirect('/reading-interest'));

        // Restore the user's reading interest
        $readingInterest->setGenres($genres);
        $entityManager->flush();
    }

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

        // Find the genre for the user
        $userReadingInterestRepository = $entityManager->getRepository(UserReadingInterest::class);
        $genre = $userReadingInterestRepository->findOneBy(['user' => $user]);
        $this->assertIsString($genre->getGenres()[0]);

        // Delete the books belonging to that genre
        $bookRepository = $entityManager->getRepository(Book::class);
        $books = $bookRepository->findBy(['category' => $genre->getGenres()[0]]);
        foreach ($books as $book) {
            $entityManager->remove($book);
        }
        $entityManager->flush();

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

    public function testRegisterATestUser()
    {
        $client = static::createClient();

        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        // Visit the registration page
        $crawler = $client->request('GET', '/register');

        // remove the test user if it exists
        $user = $userRepository->findOneBy(['email' => 'test@test.com']);
        if ($user) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        // Fill in the registration form and submit it
        $form = $crawler->filter('form[name="registration_form"]')->form([
            'registration_form[name]' => '',
            'registration_form[surname]' => '',
            'registration_form[nickname]' => 'test',
            'registration_form[email]' => 'test@test.com',
            'registration_form[agreeTerms]' => true,
            'registration_form[plainPassword]' => 'password123',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/reading-interest');
        $crawler = $client->followRedirect();

        // Fill in the reading interest form and submit it
        $form = $crawler->filter('form[name="reading_interest_form"]')->form([
            'reading_interest_form[languages]' => [LanguageEnum::ENGLISH],
            'reading_interest_form[genres]' => [GenreEnum::MYSTERY, GenreEnum::ROMANCE],
        ]);
        $client->submit($form);
    }
}