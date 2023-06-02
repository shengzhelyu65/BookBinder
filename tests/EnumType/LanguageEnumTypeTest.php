<?php

namespace App\Tests\EnumType;

use App\Doctrine\Type\LanguageEnumType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

class LanguageEnumTypeTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        if (!Type::hasType(LanguageEnumType::LANGUAGE_ENUM)) {
            Type::addType(LanguageEnumType::LANGUAGE_ENUM, LanguageEnumType::class);
        }
    }

    /**
     * @throws Exception
     */
    public function testGetSQLDeclaration(): void
    {
        $type = Type::getType(LanguageEnumType::LANGUAGE_ENUM);
        $platform = $this->createMock(AbstractPlatform::class);

        $expectedSQL = "ENUM('English','French','Spanish')";
        $actualSQL = $type->getSQLDeclaration([], $platform);

        $this->assertSame($expectedSQL, $actualSQL);
    }

    /**
     * @throws ConversionException
     * @throws Exception
     */
    public function testConvertToPHPValue(): void
    {
        $type = Type::getType(LanguageEnumType::LANGUAGE_ENUM);
        $platform = $this->createMock(AbstractPlatform::class);

        $value = 'English';
        $expectedValue = 'English';
        $actualValue = $type->convertToPHPValue($value, $platform);

        $this->assertSame($expectedValue, $actualValue);
    }

    /**
     * @throws ConversionException
     * @throws Exception
     */
    public function testConvertToDatabaseValue(): void
    {
        $type = Type::getType(LanguageEnumType::LANGUAGE_ENUM);
        $platform = $this->createMock(AbstractPlatform::class);

        $value = 'English';
        $expectedValue = 'English';
        $actualValue = $type->convertToDatabaseValue($value, $platform);

        $this->assertSame($expectedValue, $actualValue);
    }

    /**
     * @throws Exception
     */
    public function testGetName(): void
    {
        $type = Type::getType(LanguageEnumType::LANGUAGE_ENUM);

        $expectedName = 'language_enum';
        $actualName = $type->getName();

        $this->assertSame($expectedName, $actualName);
    }
}
