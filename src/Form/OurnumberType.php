<?php

namespace App\Form;

use App\Entity\Ournumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OurnumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('year', TextType::class, ['label' => 'Année de création'])
            ->add('projects', TextType::class, ['label' => 'Projets réalisés'])
            ->add('experts', TextType::class, ['label' => 'Experts techniques'])
            ->add('partner', TextType::class, ['label' => 'Partenaires'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ournumber::class,
        ]);
    }
}
