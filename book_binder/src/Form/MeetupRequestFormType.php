<?php

namespace App\Form;

use App\Entity\MeetupRequests;
use App\Entity\Library;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class MeetupRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('library_ID', EntityType::class, [
                'class' => Library::class,
                'choice_label' => 'library_name',
                'required' => true,
                'label' => 'Library',
                'constraints' => [
                    new NotBlank(),
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->orderBy('l.library_name', 'ASC');
                },
            ])
            ->add('datetime', DateTimeType::class, [
                'required' => true,
                'label' => 'Date and Time',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan([
                        'value' => 'now',
                        'message' => 'The date and time must be later than the current time.',
                    ]),
                ],
                'attr' => [
                    'min' => (new \DateTime())->format(\DateTimeInterface::ISO8601),
                ],
            ])
            ->add('maxNumber', IntegerType::class, [
                'required' => true,
                'label' => 'Maximum Number',
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 1, 'max' => 20])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MeetupRequests::class,
        ]);
    }
}
