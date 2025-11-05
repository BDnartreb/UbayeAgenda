<?php

namespace App\Form;

use App\Entity\Organisation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            // ->add('roles')
            ->add('plainPassword', PasswordType::class, [
                'label' => "Mot de passe",
                'required' => true,
                'mapped' => false,
            ])
            ->add('firstName', TextType::class, [
                'label' => "Prénom",
            ])
            ->add('lastName', TextType::class, [
                'label' => "Nom",
             ])
            ->add('phone', TextType::class, [
                'label' => "Téléphone",
            ])
            ->add('organisations', EntityType::class, [
                'label' => 'Organisation(s) (Appuyer sur la touche CTRL pour sélectionner plusieurs organisations)',
                'class' => Organisation::class,
                'choice_label' => 'name', // ou un autre champ pertinent
                'multiple' => true,
                'expanded' => false, // true = cases à cocher, false = liste déroulante multiple
                'attr' => ['placeholder' => "Sélectionner une ou plusieurs organisations.",]
            ])
            // ->add('organisations', EntityType::class, [
            //     'class' => Organisation::class,
            //     'label' => 'Organisateur',
            //     'choice_label' => 'name',
            //     'placeholder' => '— Sélectionner une organisation —',
            //     'required' => false,
            // ])
            // ->add('newOrganisation', OrganisationType::class, [
            //     'mapped' => false,
            //     'required' => false,
            //     'label' => 'Ou créer une nouvelle organisation',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
