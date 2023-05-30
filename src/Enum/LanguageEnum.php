<?php

// src/Enum/LanguageEnum.php

namespace App\Enum;

final class LanguageEnum
{
    public const ENGLISH = 'en';
    public const FRENCH = 'fr';
    public const SPANISH = 'es';

    private const CHOICES = [
        self::ENGLISH => 'English',
        self::FRENCH => 'French',
        self::SPANISH => 'Spanish',
    ];

    public static function getChoices(): array
    {
        return self::CHOICES;
    }
}
