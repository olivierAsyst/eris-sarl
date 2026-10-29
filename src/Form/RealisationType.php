<?php

namespace App\Form;

use App\Entity\Realisation;
use App\Entity\Services;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RealisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, ['label' => 'Nom du projet'])
            ->add('service', EntityType::class, [
                'class' => Services::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => '— Aucun service —',
                'label' => 'Service concerné',
                'help' => "Permet d'afficher ce projet sur la page du service et inversement.",
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 10],
            ])
            ->add('image', VichImageType::class, [
            'required' => false,
            'constraints' => [
                new Image([
                    'maxSize' => '5M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Realisation::class,
        ]);
    }
}
