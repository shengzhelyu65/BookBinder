<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Panther\PantherTestCase;


class MeetupRequestsControllerTest extends PantherTestCase
{
    public function testValidMeetupPage(): void
    {
        $id = 'RSgLXlh56JsC';
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/meetup/overview');

        // Check if expected content is present on the page
        $this->assertStringContainsString('Upcoming meetups', $crawler->filter('h5')->text());
    }

    public function testInvalidMeetupPage(): void
    {
        $id = 'a';
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/meetup/overview');

        // Check if expected content is NOT present on the page
        $this->assertStringNotContainsString('Upcoming meetups', $crawler->filter('h5')->text());
    }



}