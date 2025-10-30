<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Organisation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Enum\PublicEnum;
use App\Enum\FeeEnum;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            //->add('startDate')
            ->add('startDate', DateTimeType::class, [
                'widget' => 'single_text', // Un seul champ avec date et heure
                'html5' => true,           // Utilise le <input type="datetime-local">
                'label' => 'Date et heure de l’événement',
                'required' => true,
            ])
            ->add('description', TextType::class, ['label' => "Description",])
            ->add('poster', TextType::class, ['label' => 'Affiche'])
            //->add('fee', TextType::class, ['label' => 'Tarif'])
            ->add('fee', ChoiceType::class, [
                'label' => 'Tarif',
                'choices' => FeeEnum::cases(),
                'choice_label' => fn (FeeEnum $fee) => ucfirst($fee->value),
                'placeholder' => "Sélectionner un tarif",
            ])
            ->add('comment', TextareaType::class, ['label' => 'Commentaires'])
            ->add('thematic', TextType::class, ['label' => 'Thématique'])
            ->add('public', ChoiceType::class, [
                'choices' => PublicEnum::cases(),
                'choice_label' => fn (PublicEnum $public) => ucfirst($public->value),
                'placeholder' => "Sélectionner une catégorie de public",
            ])
            // ->add('organisation', EntityType::class, [
            //     'class' => Organisation::class,
            //     'choice_label' => 'id',
            // ])
            ->add('organisation', EntityType::class, [
                'class' => Organisation::class,
                'choice_label' => 'name',
                'placeholder' => '— Sélectionner une organisation —',
                'required' => false,
            ])
            ->add('newOrganisation', OrganisationType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Ou créer une nouvelle organisation',
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'id',
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
