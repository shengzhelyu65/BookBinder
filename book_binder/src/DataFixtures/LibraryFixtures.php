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
        $library->setEmail('bibliotheek@just.fgov.be');

        $manager->persist($library);
        $manager->flush();

        $library = new Library();
        $library->setLibraryName('Permeke Library');
        $library->setZipCode(2060);
        $library->setCity('Antwerp');
        $library->setHouseNumber(25);
        $library->setNumber('+32 3 338 38 00');
        $library->setWebsite('https://www.permeke.org/');
        $library->setEmail('permeke@antwerpen.be');

        $manager->persist($library);
        $manager->flush();

        $library = new Library();
        $library->setLibraryName('Library of the University of Antwerp');
        $library->setZipCode(2000);
        $library->setCity('Antwerp');
        $library->setHouseNumber(1);
        $library->setNumber('+32 3 265 21 11');

        
    }
}

