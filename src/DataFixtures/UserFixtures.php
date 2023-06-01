<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends Fixture
{
    public const USER_REFERENCE = 'user_ref';

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("user{$i}@example.com");
            $user->setEmail('ref@ref.com');
            $user->setPassword(''); // Password is not used in this application
            $user->setPassword(''); // Password is not used in this application
            $manager->persist($user);

            // Create unique reference for each user
            $this->addReference(self::USER_REFERENCE . $i, $user);
        }

        $manager->flush();
    }
}