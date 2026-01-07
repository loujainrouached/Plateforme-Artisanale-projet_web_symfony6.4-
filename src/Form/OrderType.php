<?php

namespace App\Form;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       $builder
                ->add('deliveryAdress', TextType::class, [
                    'label' => 'Adresse de livraison'
                    
                ])
                ->add('phoneNumber', TelType::class, [
                    'label' => 'Numéro de téléphone'
                ])
                ->add('orderHistory', HiddenType::class, [
                    'mapped' => false, // Prevent automatic mapping
                ])
                ->add('paid', HiddenType::class, [
                    'mapped' => false, // Prevent automatic mapping
                ])
             
            ;
            
       /*  $builder
            ->add('deliveryAdress')
            ->add('phoneNumber')
            ->add('DateOrder', null, [
                'widget' => 'single_text',
            ])
                  
      
            ->add('Cart', EntityType::class, [
                'class' => Cart::class,
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
            'data_class' => Order::class,
        ]);
    }
}
