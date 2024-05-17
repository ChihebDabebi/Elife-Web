<?php

namespace App\Form;

use App\Entity\Appartement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Choice;

class AppartementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numappartement')
            ->add('nomresident')
            ->add('taille')
            ->add('nbretage')
            ->add('statutappartement', ChoiceType::class, [
                'choices' => [
                    'Occupé' => 'occupied',
                    'Vacant' => 'vacant',
                ],
                'placeholder' => 'Choisissez une option',
                'constraints' => [
                    new NotBlank(['message' => 'Le statut de l\'appartement ne doit pas être vide']),
                    new Choice(['choices' => ['occupied', 'vacant'], 'message' => 'Statut de l\'appartement invalide']),
                ],
            ])
            ->add('id')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appartement::class,
        ]);
    }
}
