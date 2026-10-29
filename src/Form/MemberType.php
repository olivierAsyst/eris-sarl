<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Vich\UploaderBundle\Form\Type\VichImageType;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', null, ['label' => 'Nom complet'])
            ->add('fonction', null, ['label' => 'Fonction'])
            ->add('description', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Length([
                        'max' => 120,
                        'maxMessage' => 'La description ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('facebookLink', null, ['label' => 'Lien Facebook'])
            ->add('xlink', null, ['label' => 'Lien X (Twitter)'])
            ->add('linkedinlink', null, ['label' => 'Lien LinkedIn'])
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
            'data_class' => Member::class,
        ]);
    }
}
