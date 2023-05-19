<?php

namespace App\DataFixtures;

use App\Entity\Library;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LibraryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $library = new Library();
        $library->setLibraryName('Library of the Federal Service of Justice');
        $library->setZipCode(1000);
        $library->setCity('Brussels');
        $library->setHouseNumber(115);
        $library->setNumber('+32 2 542 65 11');
        $library->setWebsite('https://justitie.belgium.be/nl/over_de_fod_diensten/bibliotheek');

        $manager->persist($library);
        $manager->flush();
    }
}

