<?php

namespace App\Form;

use App\Entity\HomeVideo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class HomeVideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, ['label' => 'Titre de la vidéo'])
            ->add('video', VichFileType::class, [
                'label' => 'Video (MP4, AVI)',
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => '20M',
                        'mimeTypes' => ['video/mp4', 'video/x-msvideo'],
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HomeVideo::class,
        ]);
    }
}
