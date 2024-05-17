<?php

namespace App\Form;

use App\Entity\Facture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Choice;
class FactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numfacture')
            ->add('date')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Eau' => 'Eau',
                    'Gaz' => 'Gaz',
                    'Déchets' => 'Dechets',
                    'Électricité' => 'Electricite',
                ],
                'placeholder' => 'Choisissez un type',
                'constraints' => [
                    new NotBlank(['message' => 'Le type ne doit pas être vide']),
                    new Choice([
                        'choices' => ['Eau', 'Gaz', 'Déchets', 'Électricité'],
                        'message' => 'Type de facture invalide',
                    ]),
                ],
            ])
            ->add('montant')
            ->add('descriptionFacture')
            ->add('consommation')
            ->add('idAppartement')

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Facture::class,
        ]);
    }
}
