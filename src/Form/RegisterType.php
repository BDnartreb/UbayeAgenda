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
                'class' => Organisation::class,
                'choice_label' => 'name', // ou un autre champ pertinent
                'multiple' => true,
                'expanded' => true, // true = cases à cocher, false = liste déroulante multiple
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
