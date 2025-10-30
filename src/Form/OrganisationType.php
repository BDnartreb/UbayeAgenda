<?php

namespace App\Form;

use App\Entity\Organisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\StatusEnum;

class OrganisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => "Nom de l'organisation"])
            ->add('address', TextType::class, ['label' => "Adresse"])
            ->add('town', TextType::class, ['label' => "Ville"])
            ->add('email', TextType::class, ['label' => "Email"])
            ->add('phone', TextType::class, ['label' => "Téléphone"])
            //->add('status', EntityType::class, [
            ->add('status', ChoiceType::class, [
                'label' => "Type d'organisation",
                'choices' => StatusEnum::cases(),
                'choice_label' => fn (StatusEnum $status) => ucfirst($status->value),
                'placeholder' => "Sélectionner",
            ])
            // ->add('contacts', EntityType::class, [
            //     'label' => 'Personne référente',
            //     'class' => User::class,
            //     'choice_label' => 'id',
            //     'multiple' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Organisation::class,
        ]);
    }
}
