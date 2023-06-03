<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\UserPersonalInfo;
use App\Enum\GenreEnum;
use App\Enum\LanguageEnum;
use Symfony\Component\Panther\PantherTestCase;

class SettingsControllerTest extends PantherTestCase
{
    public function testSettingsPageWhenNotLoggedIn()
    {
        $client = static::createClient();
        $client->request('GET', '/settings');

        $this->assertResponseRedirects('/login');
    }

//    /**
//     * @throws NoSuchElementException|Exception
//     */
//    public function testSettingsPageWhenLoggedIn()
//    {
//        $client = static::createPantherClient();
//        $container = static::getContainer();
//
//        $userRepository = $container->get('doctrine')->getManager()->getRepository(User::class);
//        $user = $userRepository->findOneBy(['email' => 'test@test.com']);
//
//        // Login
//        $crawler = $client->request('GET', '/login');
//        $form = $crawler->filter('form.form-signin')->form();
//        $form['email'] = 'test@test.com';
//        $form['password'] = 'password123';
//        $client->submit($form);
//        $this->assertStringContainsString('/', $client->getCurrentURL());
//
//        // Send a GET request to the settings route
//        $crawler = $client->request('GET', '/settings');
//
//        // Assert that the response is successful
//        $this->assertStringContainsString('/settings', $client->getCurrentURL());
//
//        // Assert that the page contains the settings form
//        $this->assertStringContainsString('Profile Settings', $crawler->filter('h4')->text());
//        $form = $crawler->selectButton('Submit')->form();
//
//        // Fill in the form with valid data
//        $form['settings_form[name]'] = 'Test';
//        $form['settings_form[surname]'] = 'Test';
//        $form['settings_form[nickname]'] = 'test';
//        $form['settings_form[languages]'] = [LanguageEnum::ENGLISH, LanguageEnum::FRENCH];
//        $form['settings_form[genres]'] = [GenreEnum::ADULT, GenreEnum::HISTORY];
//        $client->submit($form);
//
//        // check the database for the updated userReadingInterest
//        $userReadingInterest = $user->getUserReadingInterest();
//        $this->assertSame('Test' ,$user->getUserPersonalInfo()->getName());
//        $this->assertContains(LanguageEnum::ENGLISH, $userReadingInterest->getLanguages());
//        $this->assertContains(LanguageEnum::FRENCH, $userReadingInterest->getLanguages());
//        $this->assertContains(GenreEnum::ADULT, $userReadingInterest->getGenres());
//        $this->assertContains(GenreEnum::HISTORY, $userReadingInterest->getGenres());
//    }

    public function testSettingsFormSubmission()
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $client->loginUser($user);

        // Access the settings page
        $client->request('GET', '/settings');
        $this->assertResponseIsSuccessful();

        // Submit the form with valid data
        $client->submitForm('Submit', [
            'settings_form[name]' => 'John',
            'settings_form[surname]' => 'Doe',
            'settings_form[nickname]' => 'user10',
            'settings_form[languages]' => [LanguageEnum::ENGLISH, LanguageEnum::FRENCH],
            'settings_form[genres]' => [GenreEnum::ADULT, GenreEnum::HISTORY],
        ]);

        // Assert that the form submission was successful and redirected to the profile page
        $this->assertResponseRedirects('/profile');

        // Update the crawler to the new page
        $crawler = $client->followRedirect();

        // Access the profile page and assert that the updated information is displayed
        $nickname = $crawler->filter('.col-md-6.mb-3 h1')->text();
        $this->assertSame('user10', $nickname);
        $name = $crawler->filter('.col-md-6.mb-3 p')->eq(0)->text();
        $this->assertSame('John Doe', $name);
        $languages = $crawler->filter('.col-md-6.mb-3 p')->eq(2)->text();
        $this->assertSame('en, fr', $languages);
        $genres = $crawler->filter('.col-md-6.mb-3 p')->eq(4)->text();
        $this->assertSame('adult, history', $genres);

        // Check the database for the updated userReadingInterest
        $userReadingInterest = $user->getUserReadingInterest();
        $this->assertSame('John' ,$user->getUserPersonalInfo()->getName());
        $this->assertSame('Doe' ,$user->getUserPersonalInfo()->getSurname());
        $this->assertSame('user10' ,$user->getUserPersonalInfo()->getNickName());
        $this->assertContains(LanguageEnum::ENGLISH, $userReadingInterest->getLanguages());
        $this->assertContains(LanguageEnum::FRENCH, $userReadingInterest->getLanguages());
        $this->assertContains(GenreEnum::ADULT, $userReadingInterest->getGenres());
        $this->assertContains(GenreEnum::HISTORY, $userReadingInterest->getGenres());
    }

    public function testNicknameAlreadyInUse(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user10@example.com']);
        $client->loginUser($user);

        // Access the settings page
        $client->request('GET', '/settings');
        $this->assertResponseIsSuccessful();

        // Submit the form with valid data
        $client->submitForm('Submit', [
            'settings_form[nickname]' => 'user9',
        ]);

        $this->assertResponseRedirects('/settings');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
        $this->assertSelectorTextContains('.alert.alert-danger', 'Nickname already in use');
    }
}
