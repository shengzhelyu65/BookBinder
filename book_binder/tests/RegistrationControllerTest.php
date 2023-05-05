<?php

namespace App\Tests;

use Symfony\Component\Panther\PantherTestCase;

class RegistrationControllerTest extends PantherTestCase
{
    public function testRegistration(): void
    {
        $client = static::createPantherClient();

        $crawler = $client->request('GET', '/register');

        $this->assertSelectorTextContains('h1', 'Register');

        $form = $crawler->filter('form[name=registration_form]')->form([
            'registration_form[email]' => 'john.doe@example.com',
            'registration_form[agreeTerms]' => true,
            'registration_form[plainPassword]' => 'password123',
        ]);

        $crawler = $client->submit($form);
    }
}
