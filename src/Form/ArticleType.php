<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label mt-2']
            ])

            ->add('texte', TextareaType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label mt-2']
            ])

            ->add('publie', CheckboxType::class, [
                'attr' => ['class' => 'form-check-input m-2'],
                'label_attr' => ['class' => 'form-check-label mt-1'],
                'required' => false
            ])

            ->add('date', DateTimeType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label mt-2'],
                'widget' => 'single_text'
            ])

            ->add('image', FileType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label mt-2'],

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using attributes
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/gif',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image document',
                    ])
                ],
            ])
            
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary mt-5'],
                'label' => 'Enregistrer'
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
