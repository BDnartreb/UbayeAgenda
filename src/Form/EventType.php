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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Event>
 */
class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'label' => 'Lieu',
                //'choice_label' => 'name',
                'choice_label' => function (Location $location) {
                    return $location->getName() . ' - ' . $location->getTown()->value;
                },
                'placeholder' => '— Sélectionner un lieu —',
                'required' => true,
            ])
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
            ->add('description', TextareaType::class, [
                'label' => "Description",
                'required' => false,
                'attr' => ['placeholder' => 'Taper ou coller un texte descriptif de l\'événement',],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaires',
                'required' => false,
                'attr' => ['placeholder' => 'Ajouter des informations complémentaires, liens, ...',],
            ])
            ->add('thematic', ChoiceType::class, [
                'label' => 'Thématique',
                'choices' => ThematicEnum::cases(),
                'choice_label' => fn (ThematicEnum $thematic) => ucfirst($thematic->value),
                'placeholder' => "-- Sélectionner une thématique --",
                'required' => true,
            ])
            ->add('fee', ChoiceType::class, [
                'label' => 'Tarif',
                'choices' => FeeEnum::cases(),
                'choice_label' => fn (FeeEnum $fee) => ucfirst($fee->value),
                'placeholder' => "-- Sélectionner un tarif --",
                'required' => true,
            ])
            ->add('public', ChoiceType::class, [
                'label' => 'Catégorie de public',
                'choices' => PublicEnum::cases(),
                'choice_label' => fn (PublicEnum $public) => ucfirst($public->value),
                'placeholder' => "-- Sélectionner une catégorie de public --",
                'required' => true,
            ])
            ->add('file', FileType::class, [
                'label' => 'Affiche au format .JPG ou .PNG (.PDF non accepté)',
                'mapped' => true,
                'constraints' => [
                    new Assert\File(
                        maxSize: '2M',
                        maxSizeMessage: 'Le fichier ne doit pas dépasser {{ limit }} Mo',
                        mimeTypes: ['image/jpeg', 'image/png',],
                        mimeTypesMessage: 'Le fichier doit être de type JPG ou PNG.',
                    ),
                ],
                'required' => false,
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
