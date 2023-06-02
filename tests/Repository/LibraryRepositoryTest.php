<?php

namespace App\Tests\Repository;

use App\Entity\Library;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LibraryRepositoryTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testSave()
    {
        $library = new Library();
        $library->setLibraryName('Test Library');
        $library->setZipCode(12345);
        $library->setCity('Test City');
        $library->setHouseNumber(123);
        $library->setStreet('Test Street');
        $library->setNumber('Test Number');
        $library->setWebsite('http://www.testlibrary.com');
        $library->setEmail('test@testlibrary.com');

        $this->entityManager->getRepository(Library::class)->save($library, flush: true);

        $this->assertNotNull($library->getLibraryID());
    }

    public function testSearch()
    {
        $library = $this->entityManager->getRepository(Library::class)->findOneBy(['library_name' => 'Test Library']);

        $this->assertSame($library->getCity(), 'Test City');
    }

    public function testRemove()
    {
        $library = $this->entityManager->getRepository(Library::class)->findOneBy(['library_name' => 'Test Library']);

        $this->entityManager->getRepository(Library::class)->remove($library, flush: true);

        $library = $this->entityManager->getRepository(Library::class)->findOneBy(['library_name' => 'Test Library']);
        $this->assertNull($library);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
