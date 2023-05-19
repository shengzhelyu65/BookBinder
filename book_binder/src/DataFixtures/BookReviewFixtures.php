<?php

namespace App\DataFixtures;

use App\Entity\BookReviews;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BookReviewFixtures extends Fixture
{
    /**
     * @throws OptimisticLockException
     * @throws NotSupported
     * @throws ORMException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $bookReview = new BookReviews();
            $bookReview->setBookID($faker->numberBetween(1, 1000));
            $bookReview->setRating($faker->numberBetween(1, 5));
            $bookReview->setTags($faker->words(3, true));
            $bookReview->setReview('#' . $bookReview->getTags() . '# ' . $faker->paragraphs(3, true));
            $bookReview->setCreatedAt($faker->dateTimeBetween('-1 days', 'now'));

            // Get a random user
            $user = $manager->getRepository(User::class)->findOneBy([]);
            $bookReview->setUserID($user);

            $manager->persist($bookReview);
        }

        $manager->flush();
    }
}
