<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Component\Panther\PantherTestCase;

class SecurityControllerTest extends PantherTestCase
{
    public function testLogin(): void
    {
        $client = static::createPantherClient();

        // Simulate a GET request to the /logout page
        $crawler = $client->request('GET', '/logout');

        // Assert that the user is redirected to the home page
        $this->assertStringContainsString('/login', $client->getCurrentURL());

        $this->assertStringContainsString('Sign in', $client->getPageSource());

        // Fill in the login form
        $form = $crawler->filter('form.form-signin')->form();
        $form['email'] = 'test@test.com';
        $form['password'] = 'password123';

        // Submit the form
        $crawler = $client->submit($form);

        // Assert that the reading interest form submission was successful and redirected to the home page
        $this->assertStringContainsString('/', $client->getCurrentURL());

        // Assert that the home page is displayed
        $this->assertSelectorTextContains('h4', 'mystery');
    }
}
