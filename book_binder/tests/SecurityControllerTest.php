<?php

namespace App\Tests;

use Symfony\Component\Panther\PantherTestCase;

class SecurityControllerTest extends PantherTestCase
{
    public function testLogin(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/login');

        // Check if page loaded
        $this->assertStringContainsString('sign in', $crawler->filter('h1')->text());

        // Check if login form is present
        $this->assertTrue($crawler->filter('input[type=email]')->count() > 0);
        $this->assertTrue($crawler->filter('input[type=password]')->count() > 0);
        $this->assertStringContainsString('Sign in', $crawler->filter('button')->text());

        // Check if login form works
        $form = $crawler->selectButton('Sign in')->form();
        $form['email'] = 'shengzhe.lyu@gmail.com';
        $form['password'] = 'secret';
        $client->submit($form);

        $this->assertStringContainsString('/admin', $client->getCurrentURL());
//
//        // Check if logout button is present
//        $this->assertStringContainsString('Logout', $crawler->filter('a')->text());
    }
//
//    public function testLogout(): void
//    {
//        $client = static::createPantherClient();
//        $client->request('GET', '/logout');
//
//        $this->assertStringContainsString('/', $client->getCurrentURL());
//    }
//
//    public function testGoogleLogin(): void
//    {
//        $client = static::createPantherClient();
//        $client->request('GET', '/glogin');
//
//        $this->assertSame(200, $client->getResponse()->getStatusCode());
//    }
}
