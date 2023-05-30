<?php

namespace App\Form;

use App\Entity\BookReviews;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            ->add('review', TextareaType::class, [
                'required' => true,
                'label' => 'Review',
                'constraints' => [
                    new NotBlank(),
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $bookReview = $event->getForm()->getData();
            $review = $bookReview->getReview();

            // Extract the first tag enclosed in '#'
            preg_match('/#(\w+)/', $review, $matches);
            $tag = $matches[1] ?? null;

            // Update the review and tags
            // $bookReview->setReview(str_replace("#$tag#", '', $review));
            $bookReview->setTags($tag);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BookReviews::class,
        ]);
    }

    private function extractTags(string $review): array
    {
        preg_match_all('/##(\w+)/', $review, $matches);
        return $matches[1];
    }
}
