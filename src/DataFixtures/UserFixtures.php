<?php

namespace App\DataFixtures;

use App\Entity\UserPersonalInfo;
use App\Entity\UserReadingInterest;
use App\Enum\GenreEnum;
use App\Enum\LanguageEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public const USER_REFERENCE = 'user_ref';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get all genre and language choices
        $genreChoices = GenreEnum::getChoices();
        $languageChoices = LanguageEnum::getChoices();

        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("user{$i}@example.com");
            $user->setPassword(''); // Password is not used in this application
            $manager->persist($user);

            // Create unique reference for each user
            $this->addReference(self::USER_REFERENCE . $i, $user);

            // Create UserReadingInterest object for each user
            $readingInterest = new UserReadingInterest();
            $readingInterest->setLanguages($faker->randomElements($languageChoices, $faker->numberBetween(1, 3)));
            $readingInterest->setGenres($faker->randomElements($genreChoices, $faker->numberBetween(1, 3)));
            $readingInterest->setUser($user);
            $manager->persist($readingInterest);

            // Create UserPersonalInfo object for each user
            $personalInfo = new UserPersonalInfo();
            $personalInfo->setName($faker->name());
            $personalInfo->setSurname($faker->lastName());
            $personalInfo->setNickname("User{$i}");
            $personalInfo->setUser($user);
            $manager->persist($personalInfo);
        }

        $manager->flush();
    }
}