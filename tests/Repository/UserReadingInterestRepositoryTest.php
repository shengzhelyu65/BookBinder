<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Entity\UserReadingInterest;
use App\Enum\GenreEnum;
use App\Enum\LanguageEnum;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserReadingInterestRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $userRepository;
    private $userReadingInterestRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->userReadingInterestRepository = $this->entityManager->getRepository(UserReadingInterest::class);
    }

    public function testSave()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password123');
        $user->setRoles(['ROLE_USER']);
        $this->userRepository->save($user, true);

        $readingInterest = new UserReadingInterest();
        $readingInterest->setUser($user);
        $readingInterest->setLanguages([LanguageEnum::ENGLISH]);
        $readingInterest->setGenres([GenreEnum::FANTASY]);

        $this->userReadingInterestRepository->save($readingInterest, true);

        $this->assertSame([LanguageEnum::ENGLISH], $readingInterest->getLanguages());
        $this->assertSame([GenreEnum::FANTASY], $readingInterest->getGenres());
        $this->assertNotNull($readingInterest->getId());
    }

    public function testFindUserReadingInterestByUserId()
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@example.com']);
        $readingInterest = $this->userReadingInterestRepository->findOneBy(['user' => $user]);

        $this->assertNotNull($readingInterest);
    }

    public function testRemove()
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@example.com']);

        $readingInterest = $this->userReadingInterestRepository->findOneBy(['user' => $user]);

        $this->userReadingInterestRepository->remove($readingInterest, true);

        $readingInterest = $this->userReadingInterestRepository->findOneBy(['user' => $user]);
        $this->assertNull($readingInterest);

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
