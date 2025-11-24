<?php

namespace App\Repository;

use App\Entity\Event;
use App\Enum\FeeEnum;
use App\Enum\PublicEnum;
use App\Enum\ThematicEnum;
use App\Enum\TownEnum;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findAllOrderedByStartDateFromToday(): array
    {
        $today = new \DateTime('today');

        return $this->createQueryBuilder('e')
            ->where('e.startDate >= :today')
            ->setParameter('today', $today)
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    
    public function findEventsByOrganisationOrderedByStartDateFromToday(int $id): array
    {
        $today = new \DateTime('today');

        return $this->createQueryBuilder('e')
            ->where('e.startDate >= :today')
            ->andWhere('e.organisation = :id')
            ->setParameter('today', $today)
            ->setParameter('id', $id)
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findEventsByFiltersOrderedByStartDate(
        array $organisations = [],
        array $thematics = [],
        array $fees = [],
        array $publics = [],
        array $towns = [],
        array $locations = [],
        string $date,
         ): array
        {
            $qb = $this->createQueryBuilder('e')
                ->leftJoin('e.location', 'l') 
                ->addSelect('l');;;

            if (!empty($organisations)) {
                $qb->andWhere('e.organisation IN (:organisations)')
                    ->setParameter('organisations', $organisations);
            }

            if (!empty($thematics)) {
                $thematicEnums = array_map(fn($t) => ThematicEnum::from($t), $thematics);
                $qb->andWhere('e.thematic IN (:thematics)')
                    ->setParameter('thematics', $thematicEnums);
            }

            if (!empty($fees)) {
                $feeEnums = array_map(fn($f) => FeeEnum::from($f), $fees);
                $qb->andWhere('e.fee IN (:fees)')
                    ->setParameter('fees', $feeEnums);
            }

            if (!empty($publics)) {
                $publicEnums = array_map(fn($p) => PublicEnum::from($p), $publics);
                $qb->andWhere('e.public IN (:publics)')
                    ->setParameter('publics', $publicEnums);
            }

            if (!empty($towns)) {
                $qb->andWhere('l.town IN (:towns)')
                    ->setParameter('towns', $towns);
            }

            if (!empty($locations)) {
                $qb->andWhere('e.location IN (:locations)')
                    ->setParameter('locations', $locations);
            }

            if (!empty($date)) {
                $qb->andWhere('e.startDate >= :date')
                    ->setParameter('date', new \DateTime($date));
            }

            return $qb->orderBy('e.startDate', 'ASC')
                ->getQuery()
                ->getResult();
        }


    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
