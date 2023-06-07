<?php

namespace App\DataFixtures;

use App\Entity\UserReadingList;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserReadingListFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get all the user references
        for ($i = 1; $i <= 10; $i++) {
            $users[] = $this->getReference(UserFixtures::USER_REFERENCE . $i);
        }

        // Get all the book references
        for ($i = 1; $i <= 10; $i++) {
            $books[] = $this->getReference(BookFixtures::BOOK_REFERENCE . $i)->getId();
        }

        foreach ($users as $user) {
            $readingList = new UserReadingList();
            $readingList->setUser($user);

            $fakerBooks = new ArrayCollection($books);
            $currentReadingNumber = $faker->numberBetween(1, 10 - 2);
            $wantToReadNumber = $faker->numberBetween(1, 10 - $currentReadingNumber - 1);
            $readNumber = $faker->numberBetween(1, 10 - $currentReadingNumber - $wantToReadNumber);
            $currentReadingArray = [];
            $wantToReadArray = [];
            $readArray = [];
            for ($i = 1; $i <= $currentReadingNumber; $i++) {
                $currentReadingArray[$i] = $fakerBooks->get($i-1);
            }
            for ($i = 1; $i <= $wantToReadNumber; $i++) {
                $wantToReadArray[$i-1] = $fakerBooks->get($i + $currentReadingNumber - 1);
            }
            for ($i = 1; $i <= $readNumber; $i++) {
                $readArray[$i-1] = $fakerBooks->get($i + $currentReadingNumber + $wantToReadNumber - 1);
            }
            $currentReadingArray = array_values($currentReadingArray);
            $wantToReadArray = array_values($wantToReadArray);
            $readArray = array_values($readArray);
            shuffle($currentReadingArray);
            shuffle($wantToReadArray);
            shuffle($readArray);
            $readingList->setCurrentlyReading($currentReadingArray);
            $readingList->setWantToRead($wantToReadArray);
            $readingList->setHaveRead($readArray);

            $manager->persist($readingList);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
