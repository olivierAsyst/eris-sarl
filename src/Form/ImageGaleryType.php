<?php

namespace App\Form;

use App\Entity\ImageGalery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ImageGaleryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lieu', null, ['label' => 'Lieu'])
            ->add('description', null, ['label' => 'Description (facultatif)'])
            ->add('image', VichImageType::class, [
                'required' => false,
                'allow_delete' => false, // pas de case à cocher pour supprimer
                'download_uri' => false, // pas de lien de téléchargement
                'image_uri' => true, // afficher l'image
                'label' => 'Image (JPG, PNG)',
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Image([
                        'maxSize' => '10M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                    ])
                ]
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ImageGalery::class,
        ]);
    }
}
