<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Gregwar\CaptchaBundle\Type\CaptchaType;


class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('descrirec', null, [
                'label' => 'Description',
                'attr' => ['maxlength' => 250],
                'row_attr' => ['class' => 'mb-3'], // Add a margin-bottom to the row
            ])
            ->add('categorierec', ChoiceType::class, [
                'label' => 'Category',
                'choices' => [
                    'Technical Problem' => 'Technical Problem',
                    'Reservation Problem' => 'Reservation Problem',
                    'Energy Problem' => 'Energy Problem',
                ],
                'placeholder' => 'Select a category',
                'row_attr' => ['class' => 'mb-3'], // Add a margin-bottom to the row
            ])
            ->add('imagedata', FileType::class, [
                'label' => 'Image (JPEG, PNG, GIF)',
                'mapped' => false,
                'required' => false,
                'row_attr' => ['class' => 'mb-3'], // Add a margin-bottom to the row
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
} 