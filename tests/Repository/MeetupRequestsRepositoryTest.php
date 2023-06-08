<?php

namespace App\Tests\Repository;

use App\Entity\Library;
use App\Entity\MeetupRequests;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group RepositoryTests
 */
class MeetupRequestsRepositoryTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testSave()
    {
        $meetupRequests = new MeetupRequests();
        $library = $this->entityManager->getRepository(Library::class)->findOneBy(['library_ID' => '1']);
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => '1']);
        $meetupRequests->setLibraryID($library);
        $meetupRequests->setHostUser($user);
        $meetupRequests->setDatetime(new \DateTime('now'));
        $meetupRequests->setMaxNumber(10);
        $meetupRequests->setBookID('abcd123');

        $this->entityManager->getRepository(MeetupRequests::class)->save($meetupRequests, flush: true);

        $this->assertNotNull($meetupRequests->getMeetupID());
    }

    public function testSearch()
    {
        $meetupRequests = $this->entityManager->getRepository(MeetupRequests::class)->findOneBy(['library_ID' => '1', 'host_user' => '1']);

        $this->assertSame($meetupRequests->getBookID(), 'abcd123');
    }

    public function testRemove()
    {
        $meetupRequests = $this->entityManager->getRepository(MeetupRequests::class)->findOneBy(['library_ID' => '1', 'host_user' => '1']);

        $this->entityManager->getRepository(MeetupRequests::class)->remove($meetupRequests, flush: true);

        $meetupRequests = $this->entityManager->getRepository(MeetupRequests::class)->findOneBy(['library_ID' => '1', 'host_user' => '1']);
        $this->assertNull($meetupRequests);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
