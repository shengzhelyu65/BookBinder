<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\UserReadingInterest;
use App\Entity\UserReadingList;
use App\Enum\GenreEnum;
use App\Enum\LanguageEnum;
use Symfony\Component\Panther\PantherTestCase;

class RegistrationControllerTest extends PantherTestCase
{
    public function testRegistrationProcess()
    {
        $client = static::createPantherClient();

        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $userReadingInterestRepository = $entityManager->getRepository(UserReadingInterest::class);
        $readingListRepository = $entityManager->getRepository(UserReadingList::class);

        // Visit the registration page
        $crawler = $client->request('GET', '/register');

        // remove the test user if it exists
        $user = $userRepository->findOneBy(['email' => 'test@test.com']);
        if ($user) {
            // Find the user's reading interest and delete it
            $userReadingInterest = $userReadingInterestRepository->findOneBy(['user' => $user]);
            if ($userReadingInterest) {
                $entityManager->remove($userReadingInterest);
                $entityManager->flush();
            }

            // Find the user's reading list and delete it
            $readingList = $readingListRepository->findOneBy(['user' => $user]);
            if ($readingList) {
                $entityManager->remove($readingList);
                $entityManager->flush();
            }

            $entityManager->remove($user);
            $entityManager->flush();
        }

        // Fill in the registration form and submit it
        $form = $crawler->filter('form[name="registration_form"]')->form([
            'registration_form[name]' => '',
            'registration_form[surname]' => '',
            'registration_form[nickname]' => 'test',
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
        $client->submit($form);

        // Assert that the reading interest form submission was successful and redirected to the home page
        $this->assertStringContainsString('/', $client->getCurrentURL());
        $this->assertSelectorTextContains('h4', 'mystery');

        // Assert that the user record was created in User table
        $user = $userRepository->findOneBy(['email' => 'test@test.com']);
        $this->assertInstanceOf(User::class, $user);

        // Assert that the user record was created in UserReadingInterest table
        $userReadingInterest = $userReadingInterestRepository->findOneBy(['user' => $user]);
        $this->assertInstanceOf(UserReadingInterest::class, $userReadingInterest);

        // Assert that the user record was created in UserReadingList table
        $readingList = $readingListRepository->findOneBy(['user' => $user]);
        $this->assertInstanceOf(UserReadingList::class, $readingList);
    }
}
