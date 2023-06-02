<?php

namespace App\Tests\Controller;

use App\Entity\MeetupRequestList;
use App\Entity\MeetupRequests;
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
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // Visit the page
        $crawler = $client->request('GET', '/meetup/overview');

        // Check if the page is loaded successfully
        $this->assertStringContainsString('Upcoming meetups', $crawler->filter('h4')->text());
    }

    public function testJoinMeetup(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $meetupRepository = $entityManager->getRepository(MeetupRequests::class);
        $meetupRequestListRepository = $entityManager->getRepository(MeetupRequestList::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $userId = $user->getId();

        // Find a meetup that the user is the host
        $meetup = $meetupRepository->findOneBy(['host_user' => $user]);
        $this->assertInstanceOf(MeetupRequests::class, $meetup);
        $meetupId = $meetup->getMeetupID();
        $client->request('GET', "/meetup/requests/list/join/{$userId}/{$meetupId}");
        // Check if the user is redirected to the overview page
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect('/meetup/overview'));
        // Check if there is a new entry in the meetup request list
        $meetupRequestList = $meetupRequestListRepository->findOneBy(['meetup_ID' => $meetup, 'user_ID' => $user]);
        $this->assertNull($meetupRequestList);

        // Find a meetup that the user is not the host
        $user9 = $userRepository->findOneBy(['email' => 'user9@example.com']);
        $userId9 = $user9->getId();
        $meetup = $meetupRepository->findOneBy(['host_user' => $user9]);
        // Find if the user is already a member of the meetup
        $meetupRequestList = $meetupRequestListRepository->findOneBy(['meetup_ID' => $meetup, 'user_ID' => $user]);
        if ($meetupRequestList) {
            $entityManager->remove($meetupRequestList);
            $entityManager->flush();
        }
        $meetupId = $meetup->getMeetupID();
        $client->request('GET', "/meetup/requests/list/join/{$userId}/{$meetupId}");
        // Check if the user is redirected to the overview page
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect('/meetup/overview'));
        // Check if there is a new entry in the meetup request list
        $meetupRequestList = $meetupRequestListRepository->findOneBy(['meetup_ID' => $meetup, 'user_ID' => $user]);
        $this->assertInstanceOf(MeetupRequestList::class, $meetupRequestList);
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