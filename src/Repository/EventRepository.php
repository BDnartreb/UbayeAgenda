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


    // /**
    //  * @return Event[]
    //  */
    // public function findAllOrderedByStartDateFromToday(): array
    // {
    //     $today = new \DateTime('today');
    //     $limitDate = (clone $today)->add(new \DateInterval('P15D'));
        

    //     return $this->createQueryBuilder('e')
    //         ->where('e.startDate >= :today')
    //         ->andWhere('e.startDate <= :limitDate')
    //         ->setParameter('today', $today)
    //         ->setParameter('limitDate', $limitDate)
    //         ->orderBy('e.startDate', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }

    /**
     * @return Event[]
     */
    public function findEventsForCalendar(): array
    {
        $today = new \DateTime('today');
        $daysBefore = 30;
        $daysAfter = 365;
        $periodStart = (clone $today)->sub(new \DateInterval('P' . $daysBefore . 'D'));
        $periodEnd = (clone $today)->add(new \DateInterval('P' . $daysAfter . 'D'));

        return $this->createQueryBuilder('e')
            ->where('e.startDate >= :periodStart')
            ->andWhere('e.startDate <= :periodEnd')
            ->setParameter('periodStart', $periodStart)
            ->setParameter('periodEnd', $periodEnd)
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

        /**
     * @return Event[]
     */
    public function findEventsByOrganisation(
        string $date, 
        string $organisationId
        ): array
    {
        if (!empty($date)) {
            $selectedDate = new \DateTime($date);
        }
    
        return $this->createQueryBuilder('e')
            ->where('e.startDate >= :date')
            ->andWhere('e.organisation = :organisationId')
            ->setParameter('date', $selectedDate)
            ->setParameter('organisationId', $organisationId)
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

        /**
     * @param int[] $organisations
     * @param string[] $thematics
     * @param string[] $fees
     * @param string[] $publics
     * @param TownEnum[] $towns
     * @param int[] $locations IDs des locations
     * @return Event[]
     */
    public function findEventsOrderedByStartDate(
        string $date,
        array $organisations = [],
        array $thematics = [],
        array $fees = [],
        array $publics = [],
        array $towns = [],
        array $locations = [],
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
            $thematicEnums = array_map(fn(string $t) => ThematicEnum::from($t), $thematics);
            $qb->andWhere('e.thematic IN (:thematics)')
                ->setParameter('thematics', $thematicEnums);
        }

        if (!empty($fees)) {
            $feeEnums = array_map(fn(string $f) => FeeEnum::from($f), $fees);
            $qb->andWhere('e.fee IN (:fees)')
                ->setParameter('fees', $feeEnums);
        }

        if (!empty($publics)) {
            $publicEnums = array_map(fn(string $p) => PublicEnum::from($p), $publics);
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
            $selectedDate = new \DateTime($date);
            $limitDate = (clone $selectedDate)->add(new \DateInterval('P15D'));

            $qb->andWhere('e.startDate >= :date')
                ->andWhere('e.startDate <= :limitDate')
                ->setParameter('date', $selectedDate)
                ->setParameter('limitDate', $limitDate);
        }

        return $qb->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    // /**
    //  * @param int[] $organisations
    //  * @return Event[]
    //  */
    // public function findEventsOrderedByStartDateFromSelectedDate(
    //     string $date,
    //     array $organisations = []        
    //     ): array
    //     {
    //         $qb = $this->createQueryBuilder('e');

    //         if (!empty($organisations)) {
    //             $qb->andWhere('e.organisation IN (:organisations)')
    //                 ->setParameter('organisations', $organisations);
    //         }

    //         $selectedDate = new \DateTime($date);
    //         $limitDate = (clone $selectedDate)->add(new \DateInterval('P15D'));

    //         if (!empty($date)) {
    //             $qb->andWhere('e.startDate >= :date')
    //                 ->andWhere('e.startDate <= :limitDate')
    //                 ->setParameter('date', $selectedDate)
    //                 ->setParameter('limitDate', $limitDate);
    //         }

    //         return $qb->orderBy('e.startDate', 'ASC')
    //             ->getQuery()
    //             ->getResult();
    //     }




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
