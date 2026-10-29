<?php

namespace App\Form;

use App\Entity\ContactMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subjectType', ChoiceType::class, [
                'label' => 'Type de demande',
                'choices' => array_flip(ContactMessage::SUBJECTS),
                'expanded' => true,
            ])
            ->add('fullName', TextType::class, [
                'label' => 'Nom complet',
                'attr' => ['placeholder' => 'Votre nom et prénom', 'autocomplete' => 'name', 'maxlength' => 100],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => ['placeholder' => 'votre@email.com', 'autocomplete' => 'email', 'maxlength' => 180],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone (facultatif)',
                'required' => false,
                'attr' => ['placeholder' => '+243 …', 'autocomplete' => 'tel', 'maxlength' => 30],
            ])
            ->add('organization', TextType::class, [
                'label' => 'Organisation (facultatif)',
                'required' => false,
                'attr' => ['placeholder' => 'Entreprise, ONG, institution…', 'autocomplete' => 'organization', 'maxlength' => 150],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['rows' => 6, 'placeholder' => 'Décrivez votre besoin, le lieu, les délais…', 'maxlength' => 5000],
            ])
            // Champ piège anti-robots : invisible pour les humains, doit rester vide
            ->add('website', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Ne pas remplir ce champ',
                'attr' => ['autocomplete' => 'off', 'tabindex' => '-1'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactMessage::class,
            'csrf_token_id' => 'contact_form',
        ]);
    }
}
