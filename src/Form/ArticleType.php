<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('summary', TextareaType::class,[
                'label' => 'Ajouter un résumé',
                'required' => true,
                'attr' =>[
                    'placeholder' => '',
                    'row' => 5,
                    'class' => 'comment-textarea',
                    ]
                ])
            ->add('content', TextareaType::class,[
                'label' => 'Votre article',
                'required' => true,
                'attr' =>[
                    'placeholder' => '',
                    'row' => 5,
                    'class' => 'comment-textarea',
                    ]
                ])
            ->add('image', FileType::class,[
                'label' => 'Image produit',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '3000k',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide',
                    ])
                    ],
            ])
            ->add('publication')
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
