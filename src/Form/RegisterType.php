<?php

namespace App\Form;

use App\Entity\Organisation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            // ->add('roles')
            ->add('password')
            ->add('firstName')
            ->add('lastName')
            ->add('phone')
            ->add('organisations', EntityType::class, [
                'label' => 'Organisation',
                'placeholder' => "Sélectionner une ou plusieurs organisations",
                'class' => Organisation::class,
                'choice_label' => 'name', // ou un autre champ pertinent
                'multiple' => true,
                'expanded' => false, // true = cases à cocher, false = liste déroulante multiple
            ])
            // ->add('organisations', EntityType::class, [
            //     'class' => Organisation::class,
            //     'label' => 'Organisateur',
            //     'choice_label' => 'name',
            //     'placeholder' => '— Sélectionner une organisation —',
            //     'required' => false,
            // ])
            ->add('newOrganisation', OrganisationType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Ou créer une nouvelle organisation',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
