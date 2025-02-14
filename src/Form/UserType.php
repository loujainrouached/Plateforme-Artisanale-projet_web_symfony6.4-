<?php
namespace App\Form;

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
use Symfony\Component\Validator\Constraints as Assert;


use App\Entity\User;
class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit']; // Get the flag to check if it's edit mode

        $builder
            ->add('name', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('lastname', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('email', EmailType::class, ['attr' => ['class' => 'form-control']])
         /*    ->add('dateCreation', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ]) */;

        if (!$isEdit) { // Only add these fields for new users
            $builder
            ->add('roles', ChoiceType::class, [
                'choices'  => [
                    'Client' => 'ROLE_USER',
                    'Artiste' => 'ROLE_ARTISTE',
                ],
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Sélectionnez un rôle',
                ])
                ->add('password', PasswordType::class, [
                    'label' => 'Mot de passe',
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'Le mot de passe est obligatoire.']),
                        new Assert\Length([
                            'min' => 8,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                        ]),
                    ],
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Mot de passe'],
                ])
                ->add('confirmPassword', PasswordType::class, [
                    'mapped' => false,
                    'constraints' => [new Assert\NotBlank(['message' => 'Veuillez confirmer votre mot de passe.'])],
                    'attr' => ['class' => 'form-control', 'placeholder' => 'Confirmez votre mot de passe'],
                ])
                
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
            'is_edit' => false, // Default to false, meaning it's for adding a new user
        ]);
    }
}



