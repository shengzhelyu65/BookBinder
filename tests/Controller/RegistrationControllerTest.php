<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Enum\GenreEnum;
use App\Enum\LanguageEnum;
use Symfony\Component\Panther\PantherTestCase;

class RegistrationControllerTest extends PantherTestCase
{
    public function testRegistrationProcess()
    {
        $client = static::createPantherClient();

        // Visit the registration page
        $crawler = $client->request('GET', '/register');

        // remove the test user if it exists
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@test.com']);
        if ($user) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        // Fill in the registration form and submit it
        $form = $crawler->filter('form[name="registration_form"]')->form([
            'registration_form[name]' => '',
            'registration_form[surname]' => '',
            'registration_form[nickname]' => 'test user',
            'registration_form[email]' => 'test@test.com',
            'registration_form[agreeTerms]' => true,
            'registration_form[plainPassword]' => 'password123',
        ]);
        $crawler = $client->submit($form);

        // Assert that the registration was successful and redirected to the reading interest form
        $this->assertStringContainsString('/reading-interest', $client->getCurrentURL());

        // Fill in the reading interest form and submit it
        $form = $crawler->filter('form[name="reading_interest_form"]')->form([
            'reading_interest_form[languages]' => [LanguageEnum::ENGLISH],
            'reading_interest_form[genres]' => [GenreEnum::MYSTERY, GenreEnum::ROMANCE],
        ]);
        $crawler = $client->submit($form);

        // Assert that the reading interest form submission was successful and redirected to the home page
        $this->assertStringContainsString('/', $client->getCurrentURL());

        // Assert that the home page is displayed
        $this->assertSelectorTextContains('h3', 'mystery');
    }
}
