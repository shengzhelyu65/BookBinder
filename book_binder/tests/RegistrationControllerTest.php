<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Panther\PantherTestCase;

class RegistrationControllerTest extends PantherTestCase
{
    public function testRegistration(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/register');

        // Check if page loaded
        $this->assertSelectorTextContains('h1', 'Register');

        // Check if registration form is present
        $form = $crawler->filter('form[name=registration_form]')->form([
            'registration_form[email]' => 'test@example.com',
            'registration_form[agreeTerms]' => true,
            'registration_form[plainPassword]' => 'password123',
        ]);

        // Check if registration form works (user already exists)
        $crawler = $client->submit($form);
        $this->assertSame('Register', $crawler->filter('h1')->text());

        // Check if user is created
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertInstanceOf(User::class, $user);
    }
}
