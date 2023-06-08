<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Entity\UserPersonalInfo;
use App\Entity\UserReadingInterest;
use App\Entity\UserReadingList;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group RepositoryTests
 */
class UserRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $userRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    public function testSave()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password123');
        $user->setRoles(['ROLE_USER']);

        $this->userRepository->save($user, true);

        $this->assertNotNull($user->getId());
    }

    public function testUpdatePassword()
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@example.com']);

        $newHashedPassword = 'new_hashed_password';

        $this->userRepository->upgradePassword($user, $newHashedPassword);

        $this->assertEquals($newHashedPassword, $user->getPassword());

        $savedUser = $this->userRepository->find($user->getId());
        $this->assertEquals($user->getPassword(), $savedUser->getPassword());
    }

    public function testRemove()
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@example.com']);

        $this->userRepository->remove($user, true);

        $user = $this->userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNull($user);
    }

    public function testEraseCredentials()
    {
        $this->assertNotNull($this->userRepository->findOneBy(['id' => 1])->getPassword());
    }

    public function testEntitySetInfo()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password123');
        $user->setRoles(['ROLE_USER']);
        $this->userRepository->save($user, true);

        $user1 = $this->userRepository->findOneBy(['email' => 'user1@example.com']);

        // test user personal info
        $personalInfo = new UserPersonalInfo();
        $personalInfo->setUser($user1);
        $personalInfo->setName('John');
        $personalInfo->setSurname('Doe');
        $personalInfo->setNickname('Johny');

        $user->setUserPersonalInfo($personalInfo);
        $this->assertNotNull($user->getUserPersonalInfo());
        $this->assertSame($user->getUserPersonalInfo()->getUser(), $user);

        // test user reading list
        $readingList = new UserReadingList();
        $readingList->setUser($user1);
        $readingList->setCurrentlyReading([1, 2, 3]);
        $readingList->setWantToRead([4, 5, 6]);
        $readingList->setHaveRead([7, 8, 9]);

        $user->setUserReadingList($readingList);
        $this->assertNotNull($user->getUserReadingList());
        $this->assertSame($user->getUserReadingList()->getUser(), $user);

        // test user reading interest
        $readingInterest = new UserReadingInterest();
        $readingInterest->setUser($user1);
        $readingInterest->setGenres(['Fantasy']);
        $readingInterest->setLanguages(['English']);

        $user->setUserReadingInterest($readingInterest);
        $this->assertNotNull($user->getUserReadingInterest());
        $this->assertSame($user->getUserReadingInterest()->getUser(), $user);

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
