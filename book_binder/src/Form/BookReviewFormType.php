<?php

namespace App\Form;

use App\Entity\BookReviews;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class BookReviewFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                ],
                'required' => true,
                'label' => 'Rating',
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 5,
                    ]),
                ],
            ])
            ->add('tags', null, [
                'required' => false,
                'label' => 'Tags',
            ])
            ->add('review', TextareaType::class, [
                'required' => true,
                'label' => 'Review',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BookReviews::class,
        ]);
    }
}
