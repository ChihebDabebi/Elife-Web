<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class StatFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Eau' => 'Eau',
                    'Gaz' => 'Gaz',
                    'Dechets' => 'Dechets',
                    'Electricite' => 'Electricite',
                ],

                'label' => 'Type de facture',
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
            ])
            ->add('submit', SubmitType::class, ['label' => 'Filtrer'])

            ->add('nbrEtage', NumberType::class, [
                'label' => 'Nombre d\'étages',
                'attr' => ['step' => 1], // pour permettre uniquement des nombres entiers
                'required' => false, // Rendre le champ optionnel
            ]);
    }

 


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
