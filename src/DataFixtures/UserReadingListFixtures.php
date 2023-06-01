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
            $currentReadingNumber = $faker->numberBetween(1, 10);
            $wantToReadNumber = $faker->numberBetween(1, 10 - $currentReadingNumber);
            $readNumber = $faker->numberBetween(1, 10 - $currentReadingNumber - $wantToReadNumber);
            $currentReadingArray = [];
            $wantToReadArray = [];
            $readArray = [];
            for ($i = 1; $i <= $currentReadingNumber; $i++) {
                $randomIndex = $faker->numberBetween(0, $fakerBooks->count() - 1);
                $currentReadingArray[$i] = $fakerBooks->get($randomIndex);
            }
            for ($i = 1; $i <= $wantToReadNumber; $i++) {
                $randomIndex = $faker->numberBetween(0, $fakerBooks->count() - 1);
                $wantToReadArray[$i] = $fakerBooks->get($randomIndex);
            }
            for ($i = 1; $i <= $readNumber; $i++) {
                $randomIndex = $faker->numberBetween(0, $fakerBooks->count() - 1);
                $readArray[$i] = $fakerBooks->get($randomIndex);
            }
            $currentReadingArray = array_values($currentReadingArray);
            $wantToReadArray = array_values($wantToReadArray);
            $readArray = array_values($readArray);
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
            ResetAutoincrementFixture::class,
            UserFixtures::class,
        ];
    }
}
