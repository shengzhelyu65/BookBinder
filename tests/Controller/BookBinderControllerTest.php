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
        $user = $userRepository->findOneBy(['email' => 'shengzhe.lyu@gmail.com']);
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        // test the index page
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }
}