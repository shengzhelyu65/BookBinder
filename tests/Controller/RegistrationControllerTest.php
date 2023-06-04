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
        $client->request('GET', '/logout');
        $crawler = $client->request('GET', '/register');

        // remove the test user if it exists
        $user = $userRepository->findOneBy(['email' => 'register@test.com']);
        if ($user) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        // Assert that the registration page is loaded
        $this->assertStringContainsString('/register', $client->getCurrentURL());

        // Fill in the registration form and submit it
        $form = $crawler->filter('form[name="registration_form"]')->form([
            'registration_form[name]' => '',
            'registration_form[surname]' => '',
            'registration_form[nickname]' => 'register',
            'registration_form[email]' => 'register@test.com',
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
        $user = $userRepository->findOneBy(['email' => 'register@test.com']);
        $this->assertInstanceOf(User::class, $user);

        // Assert that the user record was created in UserReadingInterest table
        $userReadingInterest = $userReadingInterestRepository->findOneBy(['user' => $user]);
        $this->assertInstanceOf(UserReadingInterest::class, $userReadingInterest);

        // Assert that the user record was created in UserReadingList table
        $readingList = $readingListRepository->findOneBy(['user' => $user]);
        $this->assertInstanceOf(UserReadingList::class, $readingList);

        // Remove the test user
        $entityManager->remove($user);
        $entityManager->remove($userReadingInterest);
    }

    public function testRegister(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Access the registration page
        $client->request('GET', '/register');

        // Check if the response is successful
        $this->assertResponseIsSuccessful();

        // Check if the user already exists in the database
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'register@test.com']);
        if ($user) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        // Fill in the registration form with valid data
        $client->submitForm('Sign up', [
            'registration_form[name]' => '',
            'registration_form[surname]' => '',
            'registration_form[nickname]' => 'register',
            'registration_form[email]' => 'register@test.com',
            'registration_form[agreeTerms]' => true,
            'registration_form[plainPassword]' => 'password123',
        ]);

        // Check if the registration was successful and the user is redirected
        $this->assertResponseRedirects('/reading-interest');

        // Follow the redirect to the reading interest form
        $client->followRedirect();

        // Fill in the reading interest form with valid data
        $client->submitForm('Submit', [
            'reading_interest_form[languages]' => [LanguageEnum::ENGLISH],
            'reading_interest_form[genres]' => [GenreEnum::MYSTERY, GenreEnum::ROMANCE],
        ]);

        // Check if the reading interest form submission was successful and the user is redirected
        $this->assertResponseRedirects('/');
    }

    public function testRegisterLoggedIn(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'register@test.com']);
        $client->loginUser($user);

        // Access the registration page
        $client->request('GET', '/register');

        // Check if it redirects to the home page
        $this->assertResponseRedirects('/');
    }

    public function testRegisterWithExistingData(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        // Access the registration page
        $client->request('GET', '/register');

        // Check if the response is successful
        $this->assertResponseIsSuccessful();

        // Fill in the registration form with valid data
        $client->submitForm('Sign up', [
            'registration_form[name]' => '',
            'registration_form[surname]' => '',
            'registration_form[nickname]' => 'register',
            'registration_form[email]' => 'register@test.com',
            'registration_form[agreeTerms]' => true,
            'registration_form[plainPassword]' => 'password123',
        ]);

        // Check if the user is redirected back to the registration page due to an existing nickname
        $this->assertResponseRedirects('/register');

        // Follow the redirect to the registration page
        $client->followRedirect();

        // Check if the error flash message is displayed
        $this->assertSelectorTextContains('.alert-danger', 'Email already registered');

        // Fill in the registration form with valid data
        $client->submitForm('Sign up', [
            'registration_form[name]' => '',
            'registration_form[surname]' => '',
            'registration_form[nickname]' => 'register',
            'registration_form[email]' => 'register@example.com',
            'registration_form[agreeTerms]' => true,
            'registration_form[plainPassword]' => 'password123',
        ]);

        // Check if the user is redirected back to the registration page due to an existing nickname
        $this->assertResponseRedirects('/register');

        // Follow the redirect to the registration page
        $client->followRedirect();

        // Check if the error flash message is displayed
        $this->assertSelectorTextContains('.alert-danger', 'Nickname already in use');
    }

    public function testRegisterWithInvalidData(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $container->get('doctrine')->getManager();

        // Access the registration page
        $client->request('GET', '/register');

        // Check if the response is successful
        $this->assertResponseIsSuccessful();

        // Fill in the registration form with valid data
        $client->submitForm('Sign up', [
            'registration_form[name]' => '',
            'registration_form[surname]' => '',
            'registration_form[nickname]' => 'invalid-user',
            'registration_form[email]' => 'test',
            'registration_form[agreeTerms]' => true,
            'registration_form[plainPassword]' => 'password123',
        ]);

        // Check if the user is redirected back to the registration page due to an existing nickname
        $this->assertResponseRedirects('/register');

        // Follow the redirect to the registration page
        $client->followRedirect();

        // Check if the error flash message is displayed
        $this->assertSelectorTextContains('.alert-danger', 'Email already registered');
    }
}
