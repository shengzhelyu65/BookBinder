<?php

namespace App\Tests\EnumType;

use App\Doctrine\Type\GenreEnumType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

/**
 * @group EnumTypesTests
 */
class GenreEnumTypeTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetSQLDeclaration(): void
    {
        $type = Type::getType(GenreEnumType::GENRE_ENUM);
        $platform = $this->createMock(AbstractPlatform::class);

        $expectedSQL = "ENUM('Adult','Anthologies','Art')";
        $actualSQL = $type->getSQLDeclaration([], $platform);
        // Extract the first 3 items from the array
        $actualSQL = substr($actualSQL, 0, 32) . ')';

        $this->assertSame($expectedSQL, $actualSQL);
    }

    /**
     * @throws ConversionException
     * @throws Exception
     */
    public function testConvertToPHPValue(): void
    {
        $type = Type::getType(GenreEnumType::GENRE_ENUM);
        $platform = $this->createMock(AbstractPlatform::class);

        $value = 'ADULT';
        $expectedValue = 'ADULT';
        $actualValue = $type->convertToPHPValue($value, $platform);

        $this->assertSame($expectedValue, $actualValue);
    }

    /**
     * @throws ConversionException
     * @throws Exception
     */
    public function testConvertToDatabaseValue(): void
    {
        $type = Type::getType(GenreEnumType::GENRE_ENUM);
        $platform = $this->createMock(AbstractPlatform::class);

        $value = 'ADULT';
        $expectedValue = 'ADULT';
        $actualValue = $type->convertToDatabaseValue($value, $platform);

        $this->assertSame($expectedValue, $actualValue);
    }

    /**
     * @throws Exception
     */
    public function testGetName(): void
    {
        $type = Type::getType(GenreEnumType::GENRE_ENUM);

        $expectedName = 'genre_enum';
        $actualName = $type->getName();

        $this->assertSame($expectedName, $actualName);
    }
}
