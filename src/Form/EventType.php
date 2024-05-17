<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Entity\Espace;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class EventType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('date', DateType::class)
            ->add('nbrPersonne',IntegerType::class)
            ->add('espace', ChoiceType::class, [
                'choices' => $this->getEspacesChoices(),
                'placeholder' => 'Sélectionnez un espace',
                'attr' => ['class' => 'form-control']
            ])
            ->add('listeInvites' ,TextType::class)
        ;
    }

    private function getEspacesChoices(): array
{
    $espaces = $this->entityManager->getRepository(Espace::class)->findAll();

    $choices = [];
    foreach ($espaces as $espace) {
        $choices[$espace->getName()] = $espace; // Utilisez l'objet Espace lui-même comme valeur
    }

    return $choices;
}


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}