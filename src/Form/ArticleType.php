<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Image;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('contenu')
            ->add('datepub', null, [
                'widget' => 'single_text',
                'data' => new \DateTime(),
                ])
                ->add('image', FileType::class, [
                    'label' => 'Image (PNG, JPG file)',
                    'mapped' => false, // Important pour ne pas lier cette donnée à l'entité
                    'required' => false,
                ])
               
                ->add('categorie', ChoiceType::class, [
                    'choices'  => [
                        'Peinture' => 'Peinture',
                        'Sculpture' => 'Sculpture',
                        'Photographie' => 'Photographie',
                        'Dessin' => 'Dessin',
                    ],
                    'placeholder' => 'Sélectionnez une catégorie',

                    'attr' => ['class' => 'form-control']
                ])
                ->add('nomAuteur', TextType::class, [
                    'label' => 'Nom de l\'auteur',
                    'attr' => ['placeholder' => 'Nom de l\'auteur']
                ]);
      //  ->add('save', SubmitType::class, ['label' => 'Ajouter']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
