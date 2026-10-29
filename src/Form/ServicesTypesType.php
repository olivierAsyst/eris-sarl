<?php

namespace App\Form;

use App\Entity\Services;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\Image;

class ServicesTypesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du service',
                'attr' => [
                    'placeholder' => 'Nom du service'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du service',
                'attr' => [
                    'placeholder' => 'Description du service',
                    'rows' => 12
                ]
            ])
            ->add('image', VichImageType::class, [
            'required' => false,
            'constraints' => [
                new Image([
                    'maxSize' => '10M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Services::class,
        ]);
    }
}
