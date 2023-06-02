<?php

namespace App\Tests\Controller;

use App\Entity\User;
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
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // test the index page
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testReviews(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Find a user
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'thomas.goris2668@gmail.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        //TODO: create a test review in the db

        // test the index page
        $crawler = $client->request('GET', '/');
        $this->assertStringContainsString('the review comment', $crawler->filter('p.card-text')->text());

        //TODO: delete the test review

    }

}