<?php

// src/Form/Type/TaskType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReadingInterestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('genre', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => [
                    'Anthologies' => 'anthologies',
                    'Art' => 'art',
                    'Audiobooks' => 'audiobooks',
                    'Biographies' => 'biographies',
                    'Body' => 'body',
                    'Business' => 'business',
                    'Children' => 'children',
                    'Comics' => 'comics',
                    'Contemporary' => 'contemporary',
                    'Cooking' => 'cooking',
                    'Crime' => 'crime',
                    'Engineering' => 'engineering',
                    'Entertainment' => 'entertainment',
                    'Fantasy' => 'fantasy',
                    'Fiction' => 'fiction',
                    'Food' => 'food',
                    'General' => 'general',
                    'Health' => 'health',
                    'History' => 'history',
                    'Horror' => 'horror',
                    'Investing' => 'investing',
                    'Literary' => 'literary',
                    'Literature' => 'literature',
                    'Manga' => 'manga',
                    'Media-help' => 'media-help',
                    'Memoirs' => 'memoirs',
                    'Mind' => 'mind',
                    'Mystery' => 'mystery',
                    'Nonfiction' => 'nonfiction',
                    'Religion' => 'religion',
                    'Romance' => 'romance',
                    'Science' => 'science',
                    'Self' => 'self',
                    'Spirituality' => 'spirituality',
                    'Sports' => 'sports',
                    'Superheroes' => 'superheroes',
                    'Technology' => 'technology',
                    'Thrillers' => 'thrillers',
                    'Travel' => 'travel',
                    'Women' => 'women',
                    'Young' => 'young',
                ],
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Language',
                'choices' => [
                    'English' => 'en',
                    'French' => 'fr',
                    'German' => 'de',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
            ]);
    }
}
