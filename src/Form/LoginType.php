<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'attr' => ['class' => 'input', 'placeholder' => 'Email'],
        ])
        ->add('password', PasswordType::class, [
            'attr' => ['class' => 'input', 'placeholder' => 'Password'],
        ])
        ->add('submit', SubmitType::class, [
            'attr' => ['class' => 'submit-btn'] 
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
 /*    public function getBlockPrefix(): string
{
    // Returning an empty string makes the form fields render without a parent name.
    return '';
} */
}
