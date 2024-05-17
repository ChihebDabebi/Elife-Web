<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content')
            ->add('datereponse', DateTimeType::class, [
                'widget' => 'single_text',
                // Configure datetime picker options if needed
            ])
            ->add('reclamation', EntityType::class, [
                'class' => Reclamation::class,
                'choice_label' => 'descrirec',
            ]);

        // Add event listener to update the reclamation's datereponse
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $comment = $event->getData();
            // Assuming 'reclamation' is the field name for the reclamation entity
            $reclamation = $comment['reclamation'];
            
            // Set the datereponse of the reclamation to the current date and time
            if ($reclamation instanceof Reclamation) {
                $reclamation->setDatereponse(new \DateTime());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
