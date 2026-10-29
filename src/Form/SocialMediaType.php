<?php

namespace App\Form;

use App\Entity\SocialMedia;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SocialMediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, ['label' => 'Nom du réseau'])
            ->add('lien', null, ['label' => 'Lien complet (https://…)'])
            ->add('image', VichFileType::class, [
            'required' => false,
            'allow_delete' => true,
            'constraints' => [
                new Image([
                    'maxSize' => '5M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                ]),
                ],
            ])
            // ->add('image', VichImageType::class, [
            //     'required' => false,
            //     'allow_delete' => true, // pas de case à cocher pour supprimer
            //     'download_uri' => false, // pas de lien de téléchargement
            //     'image_uri' => true, // afficher l'image
            //     'label' => 'Image (JPG, PNG)',
            //     'constraints' => [
            //         new \Symfony\Component\Validator\Constraints\Image([
            //             'maxSize' => '10M',
            //             'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
            //         ])
            //     ]
            // ])
            ;
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SocialMedia::class,
        ]);
    }
}
