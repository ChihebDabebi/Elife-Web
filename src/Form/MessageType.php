<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', TextType::class, [
                'attr' => [
                    'class' => 'messageField',
                    'id' => 'contenu', // Add this line
                    'placeholder' => 'Entrer votre message ici ...'
                ]
            ])
            // image picker in the form 
            ->add('image', FileType::class, [
                'label' => 'Upload Image',
                'mapped' => false, // because the entity's field is a blob, not a file
                'required' => false, // make it optional
            ])
            ->add('save',SubmitType::class)

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
