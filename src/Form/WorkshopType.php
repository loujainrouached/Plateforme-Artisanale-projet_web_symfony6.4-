<?php
// src/Form/WorkshopType.php
namespace App\Form;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

use App\Entity\Workshop;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;




class WorkshopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, [
            'label'      => 'Title',
            
        ])
        ->add('description', TextareaType::class, [
         'label' => 'Description',
                'attr' => ['placeholder' => 'Enter description'],
                'constraints' => [
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'The description cannot be longer than {{ limit }} characters.',
                    ])
                ]
            ])
      
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,  
                'required' => false, 
                'help' => $options['data']->getImage() ? '<img src="' . $options['data']->getImage() . '" alt="current photo" width="100" />' : 'No photo uploaded yet.',
                
               
            ])
            ->add('date', DateTimeType::class, [
                'label'  => 'Date',
                'widget' => 'single_text',
            ])
            ->add('type', ChoiceType::class, [
                'label'   => 'Type',
                'choices' => [
                    'Online'    => 'online',
                    'In Person' => 'in_person',
                ],
            ])
            ->add('location', TextType::class, [
                'label'    => 'Location',
                'required' => false,
                'constraints' => [
       
        new Assert\Regex([
            'pattern' => "/^[a-zA-Z0-9\s,.'-]+$/",
        ]),
    ],
            
                
            ])
            ;
    }
            

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workshop::class,
        ]);
    }
}
