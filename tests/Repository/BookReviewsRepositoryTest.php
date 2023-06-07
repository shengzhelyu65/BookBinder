<?php

namespace App\Tests\Repository;

use App\Entity\BookReviews;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookReviewsRepositoryTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testSave()
    {
        $bookReview = new BookReviews();
        $bookReview->setBookId('abcd123');
        $bookReview->setBookTitle('Test Book');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $bookReview->setUserId($user);
        $bookReview->setReview('This is a test review');
        $bookReview->setRating(4);
        $bookReview->setCreatedAt(new \DateTime('now'));
        $bookReview->setTags('test');

        $this->entityManager->getRepository(BookReviews::class)->save($bookReview, flush: true);

        $this->assertSame($bookReview->getBookId(), 'abcd123');
        $this->assertSame($bookReview->getTags(), 'test');
        $this->assertNotNull($bookReview->getReviewId());
    }

    public function testFindLatest()
    {
        $bookReviews = $this->entityManager->getRepository(BookReviews::class)->findLatest(1);

        $this->assertCount(1, $bookReviews);
        $this->assertSame($bookReviews[0]->getBookId(), 'abcd123');
    }

    public function testFindByUser()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $bookReviews = $this->entityManager->getRepository(BookReviews::class)->findByUser($user->getId());

        $this->assertGreaterThan(0, count($bookReviews));
    }

    public function testRemove()
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $bookReview = $this->entityManager->getRepository(BookReviews::class)->findOneBy(['book_title' => 'Test Book', 'user_id' => $user]);

        $this->entityManager->getRepository(BookReviews::class)->remove($bookReview, flush: true);

        $bookReview = $this->entityManager->getRepository(BookReviews::class)->findOneBy(['book_title' => 'Test Book', 'user_id' => $user]);
        $this->assertNull($bookReview);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
