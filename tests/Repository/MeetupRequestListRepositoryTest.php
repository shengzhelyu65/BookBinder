<?php

namespace App\Tests\Repository;

use App\Entity\MeetupRequestList;
use App\Entity\MeetupRequests;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group RepositoryTests
 */
class MeetupRequestListRepositoryTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testSave()
    {
        $meetupRequestList = new MeetupRequestList();

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => 1]);
        $meetupRequest = $this->entityManager->getRepository(MeetupRequests::class)->findOneBy(['meetup_ID' => 1]);
        $meetupRequestList->setUserID($user);
        $meetupRequestList->setMeetupID($meetupRequest);

        $this->entityManager->getRepository(MeetupRequestList::class)->save($meetupRequestList, flush: true);

        $this->assertNotNull($meetupRequestList->getMeetupRequestListID());
    }

    public function testSearch()
    {
        $meetupRequestList = $this->entityManager->getRepository(MeetupRequestList::class)->findOneBy(['meetup_ID' => 1, 'user_ID' => 1]);

        $this->assertNotNull($meetupRequestList->getMeetupRequestListID(), 1);
    }

    public function testRemove()
    {
        $meetupRequestList = $this->entityManager->getRepository(MeetupRequestList::class)->findOneBy(['meetup_ID' => 1, 'user_ID' => 1]);

        $this->entityManager->getRepository(MeetupRequestList::class)->remove($meetupRequestList, flush: true);
        $this->entityManager->flush();

        $meetupRequestList = $this->entityManager->getRepository(MeetupRequestList::class)->findOneBy(['meetup_ID' => 1, 'user_ID' => 1]);
        $this->assertNull($meetupRequestList);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
