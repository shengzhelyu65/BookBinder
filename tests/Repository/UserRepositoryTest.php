<?php

namespace App\Tests\Repository;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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
        $newPassword = 'newPassword123';
        $this->userRepository->updatePassword($user, $newPassword, true);
    }

    public function testRemove()
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@example.com']);

        $this->userRepository->remove($user, true);

        $user = $this->userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNull($user);
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
