<?php

namespace Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SearhControllerTest extends WebTestCase
{
    public function testClickBook(): void
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

        // Check if the page is loaded successfully
        $this->assertStringContainsString('Superhobby', $crawler->filter('h1')->text());
    }
}