<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('message', TextareaType::class, [
            'label' => 'Your Response',
            'attr' => ['class' => 'form-control'],
         
        ]);
    
        
        /*  ->add('isRead')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('reclamation', EntityType::class, [
                'class' => Reclamation::class,
                'choice_label' => 'id',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ; */
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}
