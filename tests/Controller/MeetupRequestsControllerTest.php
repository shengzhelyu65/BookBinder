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
        $userId = 33;
        $meetupRequestId = 12;
        $client->request('GET', "/meetup/requests/list/join/{$userId}/{$meetupRequestId}");
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect('/meetup/overview'));
        // TODO: check db

        // TODO: delete db entry

    }
    public function testAcceptMeetupRequest(): void
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

        $meetupRequestId = 10;
        $client->request('GET', "/meetup/request/host/accept/{$meetupRequestId}");
        $response = $client->getResponse();

        $this->assertTrue($response->isRedirect('/meetup/overview'));

    }
}