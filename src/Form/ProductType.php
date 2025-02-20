<?php

namespace App\Form;
use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'label' => 'Product Name',
            'attr' => ['placeholder' => 'Enter the product name'],
         
        ])
      
        
        ->add('description', TextType::class, [
            'label' => 'Description',
            'attr' => ['placeholder' => 'Enter the product description'],
            
         
        ])
     
        ->add('price', NumberType::class, [
            'label' => 'Price',
            'attr' => ['placeholder' => 'Enter the product price'],
                   ])
   
        
        ->add('stock', HiddenType::class, [
            'mapped' => false, // Prevent automatic mapping
        ])
     
        ->add('category', TextType::class, [
            'label' => 'Category',
            'attr' => ['placeholder' => 'Enter the product category'],
           
        ])
        ->add('status', HiddenType::class, [
            'mapped' => false, // Prevent automatic mapping
        ])
      
        ->add('image', FileType::class, [
            'label' => 'Product Image',
            'mapped' => false, 
            'required' => false, 
    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}