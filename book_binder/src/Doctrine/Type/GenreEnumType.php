<?php

// src/Doctrine/Type/LanguageEnumType.php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use App\Enum\GenreEnum;

class GenreEnumType extends Type
{
    public const GENRE_ENUM = 'genre_enum';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return "ENUM('" . implode("','", GenreEnum::getChoices()) . "')";
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
        return self::GENRE_ENUM;
    }
}

// Register the custom type
Type::addType(GenreEnumType::GENRE_ENUM, GenreEnumType::class);
