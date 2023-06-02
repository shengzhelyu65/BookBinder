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

        $this->entityManager->getRepository(BookReviews::class)->save($bookReview, flush: true);

        $this->assertSame($bookReview->getBookId(), 'abcd123');
    }

    public function testSearch()
    {
        $bookReview = $this->entityManager->getRepository(BookReviews::class)->findOneBy(['book_title' => 'Test Book', 'user_id' => 1]);

        $this->assertSame($bookReview->getBookId(), 'abcd123');
    }

    public function testRemove()
    {
        $bookReview = $this->entityManager->getRepository(BookReviews::class)->findOneBy(['book_title' => 'Test Book', 'user_id' => 1]);

        $this->entityManager->getRepository(BookReviews::class)->remove($bookReview, flush: true);

        $bookReview = $this->entityManager->getRepository(BookReviews::class)->findOneBy(['bookTitle' => 'Test Book']);
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
