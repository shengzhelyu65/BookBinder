<?php

namespace App\DataFixtures;

use App\Entity\Library;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LibraryFixtures extends Fixture implements DependentFixtureInterface
{
    public const LIBRARY_REFERENCE = 'library_ref';

    public function load(ObjectManager $manager): void
    {
        $libraries = [
            [
                'name' => 'Library of the Federal Service of Justice',
                'zipCode' => 1000,
                'city' => 'Brussels',
                'houseNumber' => 115,
                'street' => 'Waterloolaan',
                'number' => '+32 2 542 65 11',
                'website' => 'https://justitie.belgium.be/nl/over_de_fod_diensten/bibliotheek',
                'email' => 'bibliotheek@just.fgov.be',
            ],
            [
                'name' => 'Permeke Library',
                'zipCode' => 2060,
                'city' => 'Antwerp',
                'houseNumber' => 25,
                'street' => 'De Coninckplein',
                'number' => '+32 3 338 38 00',
                'website' => 'https://www.permeke.org/',
                'email' => 'permeke@antwerpen.be',
            ],
            [
                'name' => 'Library of the University of Antwerp',
                'zipCode' => 2000,
                'city' => 'Antwerp',
                'houseNumber' => 1,
                'street' => 'Prinsstraat',
                'number' => '+32 3 265 21 11',
                'website' => 'https://www.uantwerpen.be/nl/bibliotheek/',
                'email' => 'libraryUniversity@antwerpen.be',
            ],
            [
                'name' => 'KU Leuven Libraries',
                'zipCode' => 3000,
                'city' => 'Leuven',
                'houseNumber' => 1,
                'street' => 'Prinsstraat',
                'number' => '+32 16 32 46 00',
                'website' => 'https://bib.kuleuven.be/english',
                'email' => 'bib@kuleuven.be',
            ],
            [
                'name' => 'Library of the University of Ghent',
                'zipCode' => 9000,
                'city' => 'Ghent',
                'houseNumber' => 1,
                'street' => 'Sint-Hubertusstraat',
                'number' => '+32 9 264 94 55',
                'website' => 'https://www.ugent.be/en/libraries',
                'email' => 'bib@ugent.be',
            ],
        ];

        for ($i = 1; $i <= count($libraries); ++$i) {
            $library = $this->createLibrary($libraries[$i-1]);
            $manager->persist($library);

            // Create unique reference for each library
            $this->addReference(self::LIBRARY_REFERENCE . $i, $library);
        }

        $manager->flush();
    }

    private function createLibrary(array $data): Library
    {
        $library = new Library();
        $library->setLibraryName($data['name']);
        $library->setZipCode($data['zipCode']);
        $library->setCity($data['city']);
        $library->setHouseNumber($data['houseNumber']);
        $library->setStreet($data['street']);
        $library->setNumber($data['number']);
        $library->setWebsite($data['website']);
        $library->setEmail($data['email']);

        return $library;
    }

    public function getDependencies(): array
    {
        return [
            ResetAutoincrementFixture::class,
        ];
    }
}

