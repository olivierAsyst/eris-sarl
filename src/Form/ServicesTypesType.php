<?php

namespace App\Form;

use App\Entity\Services;
use App\Text\RichText;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ServicesTypesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $imageConstraints = [
            new Image([
                'maxSize' => '10M',
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
            ]),
        ];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du service',
                'attr' => [
                    'placeholder' => 'Nom du service'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du service',
                // Remplacé par l'éditeur de texte (admin/_partials/rich_text_editor.html.twig)
                'attr' => [
                    'data-rich-text' => '',
                    'rows' => 12
                ]
            ])
            ->add('image', VichImageType::class, [
                'label' => 'Image principale',
                'help' => 'Affichée sur les cartes et en haut de la page du service.',
                'required' => false,
                'constraints' => $imageConstraints,
            ])
            ->add('image2', VichImageType::class, [
                'label' => 'Deuxième image (facultative)',
                'help' => 'Affichée sous la description, sur la page du service.',
                'required' => false,
                'constraints' => $imageConstraints,
            ])
        ;

        // Anciennes descriptions en texte brut -> HTML pour l'éditeur ;
        // à l'enregistrement, seules les balises autorisées sont conservées.
        $builder->get('description')->addModelTransformer(new CallbackTransformer(
            static fn (?string $value): string => RichText::toHtml($value),
            static fn (?string $value): ?string => '' === ($clean = RichText::sanitize($value)) ? null : $clean,
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Services::class,
        ]);
    }
}
