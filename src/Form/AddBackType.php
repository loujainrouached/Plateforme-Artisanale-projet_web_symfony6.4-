<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;


use Symfony\Component\Validator\Constraints as Assert;
class AddBackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit']; // Get the flag to check if it's edit mode

        $builder
            ->add('name', TextType::class, ['attr' => ['class' => 'form-control'],
            'constraints' => [new Assert\NotBlank(['message' => 'Ce champ est obligatoire.'])],])
            ->add('lastname', TextType::class, ['attr' => ['class' => 'form-control'],
            'constraints' => [new Assert\NotBlank(['message' => 'Ce champ est obligatoire.'])],])
            ->add('email', EmailType::class, ['attr' => ['class' => 'form-control'],
            'constraints' => [new Assert\NotBlank(['message' => 'Ce champ est obligatoire.'])],])
         /*    ->add('dateCreation', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ]) */
            ->add('roles', ChoiceType::class, [
                'choices'  => [
                    'Client' => 'ROLE_CLIENT',
                    'Artiste' => 'ROLE_ARTISTE',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Sélectionnez un rôle',
                ])
                ->add('password', PasswordType::class, [
                    'label' => 'Mot de passe',
                    
                    /* 'constraints' => [
                        new Assert\Length([
                            'min' => 8,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                        ]),
                    ], */
                    'required' => false, // Make password field optional
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Laissez vide pour ne pas changer le mot de passe'],
                ])
                ->add('confirmPassword', PasswordType::class, [
                    'mapped' => false,
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Laissez vide pour ne pas changer le mot de passe '],
                    /* 'constraints' => [new Assert\NotBlank(['message' => 'Veuillez confirmer votre mot de passe.'])], */
                    'required' => false,
                ])
                ->add('photo', FileType::class, [
                    'mapped' => false,
                    'label' => 'photo',
                    'required' => false,
                    'attr' => ['class' => 'input'],
                    'help' => $options['data']->getPhoto() ? '<img src="' . $options['data']->getPhoto() . '" alt="current photo" width="100" />' : 'No photo uploaded yet.',

                ])
             /*    ->add('photoFile', VichImageType::class, [
                    'label' => 'Profile Picture',
                    'required' => false,
                    'allow_delete' => false,
                    'download_uri' => false,
                    'attr' => ['class' => 'form-control-file']
                ]) */;

        if (!$isEdit) { // Only add these fields for new users
            $builder
            
                
                
->add('dateCreation', DateTimeType::class, [
    'widget' => 'single_text',       // Renders as a single text input with proper transformation
    'html5'  => false,               // Optional: set to false if you want to control the format manually
    'attr'   => ['style' => 'display: none;'], // Hides the field
    'data'   => new \DateTime(),     // Pass the DateTime object directly
]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}
