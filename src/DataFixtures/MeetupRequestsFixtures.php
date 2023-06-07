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
        $users_shuffled = [];

        for ($i = 1; $i <= 10; $i++) {
            $meetupRequest = new MeetupRequests();
            $library = $this->getReference(LibraryFixtures::LIBRARY_REFERENCE . $faker->numberBetween(1, 5));
            $meetupRequest->setLibraryID($library);
            $maxNumber = $faker->numberBetween(5, 10);

            // Create users array with size maxNumber
            $users[0] = $this->getReference(UserFixtures::USER_REFERENCE . $i);
            for ($k = 1; count($users_shuffled) <= $maxNumber - 1; $k++) {
                // Skip the host user
                if ($k == $i) {
                    continue;
                }
                $users_shuffled[$k] = $this->getReference(UserFixtures::USER_REFERENCE . $k);
            }
            shuffle($users_shuffled);
            for ($j = 1; $j <= $maxNumber - 1; $j++) {
                $users[$j] = $users_shuffled[$j];
            }

            $meetupRequest->setMaxNumber($maxNumber);
            $meetupRequest->setHostUser($users[0]);
            $book = $this->getReference(BookFixtures::BOOK_REFERENCE . $faker->numberBetween(1, 10));
            $meetupRequest->setBookID($book->getGoogleBooksId());
            $meetupRequest->setDatetime($faker->dateTimeBetween('now', '+1 month'));

            $manager->persist($meetupRequest);

            // Create meetup request list
            $requestsNumber = $faker->numberBetween(2, $maxNumber - 1);
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
            LibraryFixtures::class,
            UserFixtures::class,
            BookFixtures::class,
        ];
    }
}
