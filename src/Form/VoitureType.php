<?php

namespace App\Form;

use App\Entity\Voiture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class VoitureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque')
            ->add('model')
            ->add('couleur')
            ->add('matricule')
            ->add('idparking')
            ->add('place_disponible', IntegerType::class, [
                'label' => 'Places disponibles',
                'mapped' => false, // Indique que ce champ n'est pas lié à une propriété de l'entité
                'disabled' => true, // Le champ est désactivé et sera mis à jour dynamiquement par JavaScript
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Voiture::class,
        ]);
    }
}