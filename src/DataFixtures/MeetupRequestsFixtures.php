<?php

namespace App\DataFixtures;

use App\Entity\MeetupList;
use App\Entity\MeetupRequestList;
use App\Entity\MeetupRequests;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MeetupRequestsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $users = [];

        for ($i = 1; $i <= 10; $i++) {
            $meetupRequest = new MeetupRequests();
            $library = $this->getReference(LibraryFixtures::LIBRARY_REFERENCE . $faker->numberBetween(1, 5));
            $meetupRequest->setLibraryID($library);
            $maxNumber = $faker->numberBetween(5, 10);

            // Create users array with size maxNumber
            for ($k = 1; $k <= $maxNumber; $k++) {
                $users[$k-1] = $this->getReference(UserFixtures::USER_REFERENCE . $k);
            }
            shuffle($users);

            $meetupRequest->setMaxNumber($maxNumber);
            $meetupRequest->setHostUser($users[0]);
            $book = $this->getReference(BookFixtures::BOOK_REFERENCE . $faker->numberBetween(1, 10));
            $meetupRequest->setBookID($book->getGoogleBooksId());
            $meetupRequest->setDatetime($faker->dateTimeBetween('now', '+1 month'));

            $manager->persist($meetupRequest);

            // Create meetup request list
            $requestsNumber = $faker->numberBetween(1, $maxNumber - 1);
            for ($j = 1; $j <= $requestsNumber; $j++) {
                $meetupRequestList = new MeetupRequestList();
                $meetupRequestList->setUserID($users[$j]);
                $meetupRequestList->setMeetupID($meetupRequest);
                $manager->persist($meetupRequestList);
            }

            // Create meetup list
            $meetupNumber = $faker->numberBetween(0, $maxNumber - $requestsNumber - 1);
            for ($j = 0; $j < $meetupNumber; $j++) {
                $meetupList = new MeetupList();
                $meetupList->setUserID($users[$requestsNumber + $j + 1]);
                $meetupList->setMeetupID($meetupRequest);
                $manager->persist($meetupList);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ResetAutoincrementFixture::class,
            LibraryFixtures::class,
            UserFixtures::class,
            BookFixtures::class,
        ];
    }
}
