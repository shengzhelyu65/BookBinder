<?php

namespace App\Tests\Controller;

use App\Entity\MeetupList;
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

        $userRepository = $entityManager->getRepository(User::class);
        $meetupRepository = $entityManager->getRepository(MeetupRequests::class);
        $meetupRequestListRepository = $entityManager->getRepository(MeetupRequestList::class);
        $meetupListRepository = $entityManager->getRepository(MeetupList::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);

        // Find a meetup that the user is the host
        $meetup = $meetupRepository->findOneBy(['host_user' => $user]);
        $this->assertInstanceOf(MeetupRequests::class, $meetup);

        // Find all the requests for the meetup
        $meetupRequestList = $meetupRequestListRepository->findBy(['meetup_ID' => $meetup]);

        // Accept the first request
        $meetupRequestId = $meetupRequestList[0]->getMeetupRequestListID();
        $meetupRequestUser = $meetupRequestList[0]->getUserID();

        // Simulate the POST request to the acceptMeetupRequest endpoint
        $client->request('POST', "/meetup/request/host/accept/{$meetupRequestId}", ['action' => 'accept']);

        // Check if the user is redirected to the overview page
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect('/meetup/overview'));

        // Check if there is a new entry in the meetup list
        $meetupList = $meetupListRepository->findOneBy(['meetup_ID' => $meetup, 'user_ID' => $meetupRequestUser]);
        $this->assertInstanceOf(MeetupList::class, $meetupList);

        // Check if the meetup request is deleted
        $meetupRequestList = $meetupRequestListRepository->findOneBy(['meetup_ID' => $meetup, 'user_ID' => $meetupRequestUser]);
        $this->assertNull($meetupRequestList);

        // Rollback the changes
        $entityManager->remove($meetupList);
        $entityManager->flush();

        $meetupRequestList = new MeetupRequestList();
        $meetupRequestList->setMeetupID($meetup);
        $meetupRequestList->setUserID($meetupRequestUser);
        $entityManager->persist($meetupRequestList);
        $entityManager->flush();
    }

    public function testRejectMeetupRequest(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $meetupRepository = $entityManager->getRepository(MeetupRequests::class);
        $meetupRequestListRepository = $entityManager->getRepository(MeetupRequestList::class);
        $meetupListRepository = $entityManager->getRepository(MeetupList::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);

        // Find a meetup that the user is the host
        $meetup = $meetupRepository->findOneBy(['host_user' => $user]);
        $this->assertInstanceOf(MeetupRequests::class, $meetup);

        // Find all the requests for the meetup
        $meetupRequestList = $meetupRequestListRepository->findBy(['meetup_ID' => $meetup]);

        // Reject the first request
        $meetupRequestId = $meetupRequestList[0]->getMeetupRequestListID();
        $meetupRequestUser = $meetupRequestList[0]->getUserID();

        // Simulate the POST request to the acceptMeetupRequest endpoint
        $client->request('POST', "/meetup/request/host/accept/{$meetupRequestId}", ['action' => 'reject']);

        // Check if the user is redirected to the overview page
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect('/meetup/overview'));

        // Check if there is no new entry in the meetup list
        $meetupList = $meetupListRepository->findOneBy(['meetup_ID' => $meetup, 'user_ID' => $meetupRequestUser]);
        $this->assertNull($meetupList);

        // Check if the meetup request is deleted
        $meetupRequestList = $meetupRequestListRepository->findOneBy(['meetup_ID' => $meetup, 'user_ID' => $meetupRequestUser]);
        $this->assertNull($meetupRequestList);

        // Rollback the changes
        $meetupRequestList = new MeetupRequestList();
        $meetupRequestList->setMeetupID($meetup);
        $meetupRequestList->setUserID($meetupRequestUser);
        $entityManager->persist($meetupRequestList);
        $entityManager->flush();
    }
}