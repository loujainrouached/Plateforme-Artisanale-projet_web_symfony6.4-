<?php

namespace App\Form;

use App\Entity\Reclamation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType; // ✅ Correct import

/* use Symfony\Component\Form\Extension\Core\Type\TimeType;
 */
class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder




        ->add('subject', TextType::class, [
            'attr' => [
                'placeholder' => 'Briefly summarize your request',
                'class' => 'contact-input', // Add custom class
                'style' => 'width: 100%; max-width: 800px;', // Adjust width

            ]
        ])

        ->add('message', TextareaType::class, [
            'attr' => [
                'placeholder' => 'Write a detailed message...',
                'cols' => 30,
                'rows' => 10,
                'class' => 'contact-textarea', // Add custom class
            ]
        ])
       /*  ->add('submit', SubmitType::class, [
            'label' => 'Submit',
            'attr' => ['class' => 'btn btn-primary']
        ]);
 */
->add('submit', SubmitType::class, [
    'label' => 'SUBMIT',
    'attr' => [
        'class' => 'custom-submit-button'
    ]
    ]);


          /*   ->add('subject')
            ->add('message')
            ->add('status')
            ->add('is_marked')
            ->add('created_at', null, [
                'widget' => 'single_text',
            ])
            /* ->add('user_id', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ]) 
        ; */
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
