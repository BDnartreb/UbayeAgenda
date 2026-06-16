<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use App\Repository\LocationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Enum\PublicEnum;
use App\Enum\FeeEnum;
use App\Enum\ThematicEnum;
use App\Enum\EventStatusEnum;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Event>
 */
class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $organisation = $options['organisation'];

        $builder
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'label' => 'Lieu',
                
                'query_builder' => function (LocationRepository $lr) use ($organisation) {
                    return $lr->createQueryBuilder('l')
                        ->where('l.organisation = :organisation')
                        ->setParameter('organisation', $organisation)
                        ->orderBy('l.name', 'ASC');
                },

                //'choice_label' => 'name',
                'choice_label' => function (Location $location) {
                    return $location->getName() . ' - ' . $location->getTown()->value;
                },
                'placeholder' => '— Sélectionner un lieu —',
                'required' => true,
            ])
            ->add('name', TextType::class, ['label' => "Nom de l'événement", 'required' => true,])
            ->add('startDate', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'label' => 'Début',
                'required' => true,
            ])
            ->add('endDate', TimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'label' => 'Heure de fin',
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
            ->add('eventstatus', ChoiceType::class, [
                'label' => 'Niveau de diffusion',
                'choices' => EventStatusEnum::cases(),
                // 'choice_label' => fn (EventStatusEnum $eventStatus) => ucfirst($eventStatus->value),
                // 'choice_label' => fn (EventStatusEnum $choice) => $choice->value,
                    'choice_label' => fn (EventStatusEnum $choice) =>
                        match ($choice) {
                            EventStatusEnum::DRAFT => 'Mode Brouillon (Organisateur uniquement)',
                            EventStatusEnum::ORGANISATIONS => 'Interne (Organisateur et Calendrier commun)',
                            EventStatusEnum::PUBLIC => 'Public (Agenda et Calendrier commun)',
                        },
                'choice_value' => fn (?EventStatusEnum $choice) => $choice?->name,
                'placeholder' => "-- Sélectionner un niveau de diffusion --",
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'organisation' => null,
        ]);
    }
}
