<?php

namespace App\Form;

use App\Entity\UserPersonalInfo;
use App\Entity\UserReadingInterest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\LanguageEnum;
use App\Enum\GenreEnum;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['required' => false])
            ->add('surname', TextType::class, ['required' => false])
            ->add('nickname', TextType::class, ['required' => false])
            ->add('languages', ChoiceType::class, [
                'label' => 'Languages',
                'choices' => array_flip(LanguageEnum::getChoices()),
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'multiple-select-field',
                    'data-placeholder' => 'Select book languages',
                ]
            ])
            ->add('genres', ChoiceType::class, [
                'label' => 'Genres',
                'choices' => array_flip(GenreEnum::getChoices()),
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'multiple-select-field',
                    'data-placeholder' => 'Search and select book genres',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'userPersonalInfo_data' => UserPersonalInfo::class,
            'readingInterest_data' => UserReadingInterest::class
        ]);
    }
}
