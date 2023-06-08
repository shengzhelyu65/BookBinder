<?php

namespace App\Tests\Repository;

use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group RepositoryTests
 */
class BookRepositoryTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testSave()
    {
        $book = new Book();
        $book->setGoogleBooksId('abcd123');
        $book->setTitle('Test Book');
        $book->setDescription('This is a test book');
        $book->setThumbnail('https://via.placeholder.com/150');
        $book->setRating(4);
        $book->setReviewCount(100);
        $book->setAuthor('Test Author');
        $book->setPages(100);
        $book->setPublishedDate(new \DateTime('now'));
        $book->setCategory('Test Category');

        $this->entityManager->getRepository(Book::class)->save($book, flush: true);

        $this->assertSame($book->getGoogleBooksId(), 'abcd123');
        $this->assertSame($book->getCategory(), 'Test Category');
        $this->assertSame($book->getRating(), 4);
        $this->assertSame($book->getReviewCount(), 100);
        $this->assertNotNull($book->getId());
    }

    public function testSearch()
    {
        $book = $this->entityManager->getRepository(Book::class)->findOneBy(['title' => 'Test Book']);

        $this->assertSame($book->getGoogleBooksId(), 'abcd123');
    }

    public function testRemove()
    {
        $book = $this->entityManager->getRepository(Book::class)->findOneBy(['title' => 'Test Book']);

        $this->entityManager->getRepository(Book::class)->remove($book, flush: true);

        $book = $this->entityManager->getRepository(Book::class)->findOneBy(['title' => 'Test Book']);
        $this->assertNull($book);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
