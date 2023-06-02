<?php

namespace App\Tests\Repository;

use App\Entity\MeetupList;
use App\Entity\MeetupRequests;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MeetupListRepositoryTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testSave()
    {
        $meetupList = new MeetupList();

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => 1]);
        $meetupRequest = $this->entityManager->getRepository(MeetupRequests::class)->findOneBy(['meetup_ID' => 1]);
        $meetupList->setUserID($user);
        $meetupList->setMeetupID($meetupRequest);

        $this->entityManager->getRepository(MeetupList::class)->save($meetupList, flush: true);

        $this->assertNotNull($meetupList->getMeetupListID());
    }

    public function testSearch()
    {
        $meetupList = $this->entityManager->getRepository(MeetupList::class)->findOneBy(['meetup_ID' => 1, 'user_ID' => 1]);

        $this->assertNotNull($meetupList->getMeetupListID(), 1);
    }

    public function testRemove()
    {
        $meetupList = $this->entityManager->getRepository(MeetupList::class)->findOneBy(['meetup_ID' => 1, 'user_ID' => 1]);

        $this->entityManager->getRepository(MeetupList::class)->remove($meetupList, flush: true);
        $this->entityManager->flush();

        $meetupList = $this->entityManager->getRepository(MeetupList::class)->findOneBy(['meetup_ID' => 1, 'user_ID' => 1]);
        $this->assertNull($meetupList);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
