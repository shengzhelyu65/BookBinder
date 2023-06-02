<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Entity\UserReadingList;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserReadingListRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $userRepository;
    private $userReadingListRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->userReadingListRepository = $this->entityManager->getRepository(UserReadingList::class);
    }

    public function testSave()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password123');
        $user->setRoles(['ROLE_USER']);
        $this->userRepository->save($user, true);

        $userReadingList = new UserReadingList();
        $userReadingList->setUser($user);
        $userReadingList->setCurrentlyReading([1, 2, 3]);
        $userReadingList->setWantToRead([4, 5, 6]);
        $userReadingList->setHaveRead([7, 8, 9]);

        $this->userReadingListRepository->save($userReadingList, true);

        $this->assertNotNull($user->getId());
    }

    public function testFindUserReadingListByUserId()
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@example.com']);
        $readingList = $this->userReadingListRepository->findOneBy(['user' => $user]);

        $this->assertNotNull($readingList);
    }

    public function testRemove()
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@example.com']);

        $readingList = $this->userReadingListRepository->findOneBy(['user' => $user]);

        $this->userReadingListRepository->remove($readingList, true);

        $readingList = $this->userReadingListRepository->findOneBy(['user' => $user]);
        $this->assertNull($readingList);

        $this->userRepository->remove($user, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
        $this->userRepository = null;
    }
}
