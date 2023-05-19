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
        $library->setStreet('Waterloolaan');
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
        $library->setStreet('De Coninckplein');
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
        $library->setStreet('Prinsstraat');
        $library->setNumber('+32 3 265 21 11');
        $library->setWebsite('https://www.uantwerpen.be/nl/bibliotheek/');
        $library->setEmail('libraryUniversity@antwerpen.be');

        $manager->persist($library);
        $manager->flush();

        $library = new Library();
        $library->setLibraryName('KU Leuven Libraries');
        $library->setZipCode(3000);
        $library->setCity('Leuven');
        $library->setHouseNumber(1);
        $library->setStreet('Prinsstraat');
        $library->setNumber('+32 16 32 46 00');
        $library->setWebsite('https://bib.kuleuven.be/english');
        $library->setEmail('bib@kuleuven.be');
    }
}

