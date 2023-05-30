<?php

// src/Doctrine/Type/LanguageEnumType.php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use App\Enum\LanguageEnum;

class LanguageEnumType extends Type
{
    public const LANGUAGE_ENUM = 'language_enum';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return "ENUM('" . implode("','", LanguageEnum::getChoices()) . "')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value;
    }

    public function getName(): string
    {
        return self::LANGUAGE_ENUM;
    }
}

// Register the custom type
Type::addType(LanguageEnumType::LANGUAGE_ENUM, LanguageEnumType::class);
