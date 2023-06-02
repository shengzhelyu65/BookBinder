<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Entity\UserPersonalInfo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserPersonalInfoRepositoryTest extends KernelTestCase
{
    private $entityManager;
    private $userRepository;
    private $userPersonalInfoRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->userPersonalInfoRepository = $this->entityManager->getRepository(UserPersonalInfo::class);
    }

    public function testSave()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password123');
        $user->setRoles(['ROLE_USER']);
        $this->userRepository->save($user, true);

        $personalInfo = new UserPersonalInfo();
        $personalInfo->setUser($user);
        $personalInfo->setName('John');
        $personalInfo->setSurname('Doe');
        $personalInfo->setNickname('Johny');

        $this->userPersonalInfoRepository->save($personalInfo, true);

        $this->assertNotNull($personalInfo->getId());
    }

    public function testFindUserPersonalInfoByUserId()
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@example.com']);
        $userPersonalInfo = $this->userPersonalInfoRepository->findOneBy(['user' => $user]);

        $this->assertNotNull($userPersonalInfo);
    }

    public function testRemove()
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@example.com']);

        $personalInfo = $this->userPersonalInfoRepository->findOneBy(['user' => $user]);

        $this->userPersonalInfoRepository->remove($personalInfo, true);

        $personalInfo = $this->userPersonalInfoRepository->findOneBy(['user' => $user]);
        $this->assertNull($personalInfo);

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
