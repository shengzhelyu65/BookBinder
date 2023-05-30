<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MeetupRequestsControllerTest extends WebTestCase
{
    public function testMeetupOverview(): void
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
        $crawler = $client->request('GET', '/meetup/overview');

        // Check if the page is loaded successfully
        $this->assertStringContainsString('Upcoming meetups', $crawler->filter('h5')->text());
    }
}