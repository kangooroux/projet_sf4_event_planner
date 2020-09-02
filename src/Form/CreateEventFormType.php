<?php

namespace App\Form;

use App\Entity\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateEventFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom de l\'événement est manquant'
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le nom de l\'événement doit contenir {{ limit }} caractères au minimum',
                        'max' => 200,
                        'maxMessage' => 'Le nom de l\'événement ne peut contenir plus de {{ limit }} caractères'
                    ]),
                ]
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Description manquante'
                    ]),
                ]
            ])
            ->add('location', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'adresse est manquante'
                    ]),
                ]
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date est manquante'
                    ]),
                    new GreaterThan("today +7 days"),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
