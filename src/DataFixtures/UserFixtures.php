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
        $existingUser = $manager->getRepository(User::class)->findOneBy(['email' => 'ref@ref.com']);

        if ($existingUser) {
            // User already exists, remove it first
            $manager->remove($existingUser);
            $manager->flush();
        }

        // Create and persist user objects
        $user = new User();
        $user->setEmail('ref@ref.com');
        $user->setPassword(''); // Password is not used in this application
        // $manager->persist($user);

        // other fixtures can get this object using the UserFixtures::USER_REFERENCE constant
        $this->addReference(self::USER_REFERENCE, $user);

        // $manager->flush();
    }
}