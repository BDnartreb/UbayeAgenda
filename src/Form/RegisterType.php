<?php

namespace App\Form;

use App\Entity\Organisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\StatusEnum;
use App\Enum\TownEnum;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<Organisation>
 */
class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => "Nom de l'organisation"])
            ->add('address', TextType::class, ['label' => "Adresse"])
            ->add('town', ChoiceType::class, [
                'label' => "Commune",
                'choices' => TownEnum::cases(),
                'choice_label' => fn (TownEnum $town) => ucfirst($town->value),
                'placeholder' => "Sélectionner une Commune",
            ])
            ->add('email', TextType::class, ['label' => "Email"])
            ->add('phone', TextType::class, ['label' => "Téléphone"])
            ->add('status', ChoiceType::class, [
                'label' => "Type d'organisation",
                'choices' => StatusEnum::cases(),
                'choice_label' => fn (StatusEnum $status) => ucfirst($status->value),
                'placeholder' => "Sélectionner un statut",
            ])
            ->add('firstName', TextType::class, [
                'label' => "Prénom du contact",
            ])
            ->add('lastName', TextType::class, [
                'label' => "Nom du contact",
             ])
            ->add('plainPassword', PasswordType::class, [
                'label' => "Mot de passe",
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: 'Ce champ doit être renseigné'),
                ],
            ])          
            
            
            // ->add('organisations', EntityType::class, [
            //     'label' => 'Organisation(s) (Appuyer sur la touche CTRL pour sélectionner plusieurs organisations)',
            //     'class' => Organisation::class,
            //     'choice_label' => 'name', // ou un autre champ pertinent
            //     'multiple' => true,
            //     'expanded' => false, // true = cases à cocher, false = liste déroulante multiple
            //     'attr' => ['placeholder' => "Sélectionner une ou plusieurs organisations.",]
            // ])

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
            'data_class' => Organisation::class,
        ]);
    }
}
