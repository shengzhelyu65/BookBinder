<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\UserReadingList;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    public function testMyProfile(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $client->loginUser($user);

        // Send a GET request to the profile route
        $client->request('GET', '/profile');

        // Assert that the response is successful
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $user->getUserPersonalInfo()->getNickname());
    }

    public function testUserProfile()
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $client->loginUser($user);

        // Send a GET request to the profile route
        $user9 = $userRepository->findOneBy(['email' => 'user9@example.com']);
        $user9Nickname = $user9->getUserPersonalInfo()->getNickname();
        $client->request('GET', "/profile/{$user9Nickname}");

        // Assert that the response is successful
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $user9->getUserPersonalInfo()->getNickname());

        // Send a GET request to the profile route
        $userNickname = $user->getUserPersonalInfo()->getNickname();
        $client->request('GET', "/profile/{$userNickname}");

        // Assert if the user is redirected to their own profile
        $this->assertResponseRedirects('/profile');

        // Send a GET request to the profile route
        $invalidUsername = 'invalid-username';
        $client->request('GET', "/profile/{$invalidUsername}");

        // Assert if the user is redirected to the home page
        $this->assertResponseRedirects('/');
    }
}
