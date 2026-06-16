<?php

namespace App\Form;

use App\Entity\Location;
use App\Enum\TownEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Location>
 */
class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom du lieu'])
            ->add('address', TextType::class, ['label' => 'Adresse'])
            ->add('town', ChoiceType::class, [
                'label' => 'Commune',
                'choices' => TownEnum::cases(),
                'choice_label' => fn (TownEnum $town) => ucfirst($town->value),
                'placeholder' => 'Selectionner une commune',
                'required' => true,
            ])
            // ->add('lat', TextType::class, [
            //     'label' => 'Latitude',
            //     'required' => false,
            //     'attr' => ['placeholder' => 'exple : 48.856613',
            //     ],
            // ])
            // ->add('lon', TextType::class, [
            //     'label' => 'Longitude',
            //     'required' => false,
            //     'attr' => ['placeholder' => 'exple : 2.352222',
            //     ],
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
