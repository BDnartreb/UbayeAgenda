<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Enum\PublicEnum;
use App\Enum\FeeEnum;
use App\Enum\ThematicEnum;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => "Nom de l'événement", 'required' => true,])
            //->add('startDate')
            ->add('startDate', DateTimeType::class, [
                'widget' => 'single_text', // Un seul champ avec date et heure
                'html5' => true,           // Utilise le <input type="datetime-local">
                'label' => 'Début',
                'required' => true,
            ])
            ->add('endDate', DateTimeType::class, [
                'widget' => 'single_text', // Un seul champ avec date et heure
                'html5' => true,           // Utilise le <input type="datetime-local">
                'label' => 'Fin',
                'required' => false,
            ])
            ->add('description', TextType::class, ['label' => "Description", 'required' => false,])
            ->add('poster', TextType::class, ['label' => 'Affiche', 'required' => false,])
            ->add('fee', ChoiceType::class, [
                'label' => 'Tarif',
                'choices' => FeeEnum::cases(),
                'choice_label' => fn (FeeEnum $fee) => ucfirst($fee->value),
                'placeholder' => "Sélectionner un tarif",
                'required' => true,
            ])
            ->add('comment', TextareaType::class, ['label' => 'Commentaires','required' => false,])
            ->add('thematic', ChoiceType::class, [
                'label' => 'Thématique',
                'choices' => ThematicEnum::cases(),
                'choice_label' => fn (ThematicEnum $thematic) => ucfirst($thematic->value),
                'placeholder' => "Sélectionner une thématique",
                'required' => true,
            ])
            ->add('public', ChoiceType::class, [
                'label' => 'Catégorie de public',
                'choices' => PublicEnum::cases(),
                'choice_label' => fn (PublicEnum $public) => ucfirst($public->value),
                'placeholder' => "Sélectionner une catégorie de public",
                'required' => true,
            ])
            // ->add('organisation', EntityType::class, [
            //     'class' => Organisation::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('organisation', EntityType::class, [
            //     'class' => Organisation::class,
            //     'choice_label' => 'name',
            //     'placeholder' => '— Sélectionner une organisation —',
            //     'required' => true,
            // ])
            // ->add('newOrganisation', OrganisationType::class, [
            //     'mapped' => false,
            //     'required' => false,
            //     'label' => 'Ou créer une nouvelle organisation',
            // ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'name',
                'placeholder' => '— Sélectionner un lieu —',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
