<?php

namespace App\Form;

use App\Enum\LanguageEnum;
use App\Enum\GenreEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReadingInterestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('languages', ChoiceType::class, [
                'label' => 'Languages',
                'choices' => array_flip(LanguageEnum::getChoices()),
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'multiple-select-field',
                    'data-placeholder' => 'Select languages',
                ]
            ])
            ->add('genres', ChoiceType::class, [
                'label' => 'Genres',
                'choices' => array_flip(GenreEnum::getChoices()),
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'multiple-select-field',
                    'data-placeholder' => 'Search and Select genres',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'readingInterest_data' => ReadingInterest::class,
        ]);
    }
}
