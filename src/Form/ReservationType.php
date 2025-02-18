<?php
namespace App\Form;

use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Workshop;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;  // Add the DateType import
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;


class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('seatsReserved')
            ->add('notes', TextareaType::class, [
                'constraints' => [
                    new Length([
                        'max' => 30,
                        'maxMessage' => 'Notes cannot be longer than 30 characters.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Max 30 characters'
                ],
                'required' => false,
            ])
            ->add('dateReservation', HiddenType::class, [
                'mapped' => false, // This prevents automatic mapping since we need to convert it later
            ])
            ->add('uniqueCode', HiddenType::class, [
                'mapped' => false, // Prevent automatic mapping
            ])
            ->add('user', HiddenType::class, [
                'mapped' => false, 
            ])
            ->add('workshop', HiddenType::class, [
                'mapped' => false,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
