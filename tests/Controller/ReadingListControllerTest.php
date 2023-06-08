<?php

namespace App\Tests\Controller;

use Symfony\Component\Panther\PantherTestCase;
use App\Entity\UserReadingList;
use App\Entity\User;

class ReadingListControllerTest extends PantherTestCase
{
    /**
     * @group PantherTestCase
     */
    public function testShowReadingList(): void
    {
        $client = static::createClient();
        $container = self::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $readingListRepository = $entityManager->getRepository(UserReadingList::class);

        // Get the logged in user
        $user = $userRepository->findOneBy(['email' => 'user3@example.com']);
        $client->loginUser($user);

        // view the reading list
        $crawler = $client->request('GET', '/reading-list');

        // Assert that the response is successful
        $this->assertResponseIsSuccessful();

        // Find the reading list of the user
        $readingList = $readingListRepository->findOneBy(['user' => $user]);
        // Get the number of books in each array
        $numberOfCurrentlyReadingBooks = count($readingList->getCurrentlyReading());
        $numberOfWantToReadBooks = count($readingList->getWantToRead());
        $numberOfHaveReadBooks = count($readingList->getHaveRead());

        // Assert that the "Want to Read" section is displayed correctly
        $firstDiv = $crawler->filter('div.d-flex.flex-row.overflow-auto.gx-3')->eq(0);
        $divDescription = $firstDiv->filter('h4')->text();
        $this->assertSame('Want to Read', $divDescription);
        $cardCount = $firstDiv->filter('.card')->count();
        $this->assertSame($numberOfWantToReadBooks, $cardCount);

        // Assert that the "Currently Reading" section is displayed correctly
        $secondDiv = $crawler->filter('div.d-flex.flex-row.overflow-auto.gx-3')->eq(1);
        $divDescription = $secondDiv->filter('h4')->text();
        $this->assertSame('Currently Reading', $divDescription);
        $cardCount = $secondDiv->filter('.card')->count();
        $this->assertSame($numberOfCurrentlyReadingBooks, $cardCount);

        // Assert that the "Have Read" section is displayed correctly
        $thirdDiv = $crawler->filter('div.d-flex.flex-row.overflow-auto.gx-3')->eq(2);
        $divDescription = $thirdDiv->filter('h4')->text();
        $this->assertSame('Have Read', $divDescription);
        $cardCount = $thirdDiv->filter('.card')->count();
        $this->assertSame($numberOfHaveReadBooks, $cardCount);
    }
}
