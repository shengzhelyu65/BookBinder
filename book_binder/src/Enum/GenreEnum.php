<?php

namespace App\Enum;

final class GenreEnum
{
    public const ADULT = 'adult';
    public const ANTHOLOGIES = 'anthologies';
    public const ART = 'art';
    public const AUDIOBOOKS = 'audiobooks';
    public const BIOGRAPHIES = 'biographies';
    public const BODY = 'body';
    public const BUSINESS = 'business';
    public const CHILDREN = 'children';
    public const COMICS = 'comics';
    public const CONTEMPORARY = 'contemporary';
    public const COOKING = 'cooking';
    public const CRIME = 'crime';
    public const ENGINEERING = 'engineering';
    public const ENTERTAINMENT = 'entertainment';
    public const FANTASY = 'fantasy';
    public const FICTION = 'fiction';
    public const FOOD = 'food';
    public const GENERAL = 'general';
    public const HEALTH = 'health';
    public const HISTORY = 'history';
    public const HORROR = 'horror';
    public const INVESTING = 'investing';
    public const LITERARY = 'literary';
    public const LITERATURE = 'literature';
    public const MANGA = 'manga';
    public const MEDIA_HELP = 'media-help';
    public const MEMOIRS = 'memoirs';
    public const MIND = 'mind';
    public const MYSTERY = 'mystery';
    public const NONFICTION = 'nonfiction';
    public const RELIGION = 'religion';
    public const ROMANCE = 'romance';
    public const SCIENCE = 'science';
    public const SELF = 'self';
    public const SPIRITUALITY = 'spirituality';
    public const SPORTS = 'sports';
    public const SUPERHEROES = 'superheroes';
    public const TECHNOLOGY = 'technology';
    public const THRILLERS = 'thrillers';
    public const TRAVEL = 'travel';
    public const WOMEN = 'women';
    public const YOUNG = 'young';

    private const CHOICES = [
        self::ADULT => 'Adult',
        self::ANTHOLOGIES => 'Anthologies',
        self::ART => 'Art',
        self::AUDIOBOOKS => 'Audiobooks',
        self::BIOGRAPHIES => 'Biographies',
        self::BODY => 'Body',
        self::BUSINESS => 'Business',
        self::CHILDREN => 'Children',
        self::COMICS => 'Comics',
        self::CONTEMPORARY => 'Contemporary',
        self::COOKING => 'Cooking',
        self::CRIME => 'Crime',
        self::ENGINEERING => 'Engineering',
        self::ENTERTAINMENT => 'Entertainment',
        self::FANTASY => 'Fantasy',
        self::FICTION => 'Fiction',
        self::FOOD => 'Food',
        self::GENERAL => 'General',
        self::HEALTH => 'Health',
        self::HISTORY => 'History',
        self::HORROR => 'Horror',
        self::INVESTING => 'Investing',
        self::LITERARY => 'Literary',
        self::LITERATURE => 'Literature',
        self::MANGA => 'Manga',
        self::MEDIA_HELP => 'Media-help',
        self::MEMOIRS => 'Memoirs',
        self::MIND => 'Mind',
        self::MYSTERY => 'Mystery',
        self::NONFICTION => 'Nonfiction',
        self::RELIGION => 'Religion',
        self::ROMANCE => 'Romance',
        self::SCIENCE => 'Science',
        self::SELF => 'Self',
        self::SPIRITUALITY => 'Spirituality',
        self::SPORTS => 'Sports',
        self::SUPERHEROES => 'Superheroes',
        self::TECHNOLOGY => 'Technology',
        self::THRILLERS => 'Thrillers',
        self::TRAVEL => 'Travel',
        self::WOMEN => 'Women',
        self::YOUNG => 'Young',
    ];

    public static function getChoices(): array
    {
        return self::CHOICES;
    }
}
