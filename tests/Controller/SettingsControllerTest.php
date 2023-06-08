<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\UserPersonalInfo;
use App\Enum\GenreEnum;
use App\Enum\LanguageEnum;
use Symfony\Component\Panther\PantherTestCase;

class SettingsControllerTest extends PantherTestCase
{
    /**
     * @group WebTestCase
     */
    public function testSettingsPageWhenNotLoggedIn()
    {
        $client = static::createClient();
        $client->request('GET', '/settings');

        $this->assertResponseRedirects('/login');
    }

    /**
     * @group WebTestCase
     */
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
    }

    /**
     * @group WebTestCase
     */
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
