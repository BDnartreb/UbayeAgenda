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

    /**
     * @return Event[]
     */
    public function findEventsForCalendar(): array
    {
        //$today = new \DateTimeImmutable();
        // $today = new \DateTime('today');
        // $periodStart = $today->modify('-30 days');
        // $periodEnd = $today->modify('+365 days');

        // return $this->createQueryBuilder('e')
        //     ->select('e.id, e.startDate, e.name')
        //     ->where('e.startDate BETWEEN :periodStart AND :periodEnd')
        //     ->setParameter('periodStart', $periodStart)
        //     ->setParameter('periodEnd', $periodEnd)
        //     ->orderBy('e.startDate', 'ASC')
        //     ->getQuery()
        //     ->getResult();
    
        $today = new \DateTime('today');
        $daysBefore = 30;
        $daysAfter = 365;
        $periodStart = (clone $today)->sub(new \DateInterval('P' . $daysBefore . 'D'));
        $periodEnd = (clone $today)->add(new \DateInterval('P' . $daysAfter . 'D'));

        return $this->createQueryBuilder('e')
            ->where('e.startDate BETWEEN :periodStart AND :periodEnd')
            // ->where('e.startDate >= :periodStart')
            // ->andWhere('e.startDate <= :periodEnd')
            ->setParameter('periodStart', $periodStart)
            ->setParameter('periodEnd', $periodEnd)
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string|null $date
     * @param int $organisationId
     * 
     * @return Event[] 
     */
    public function findEventsByOrganisation(
        ?string $date, 
        int $organisationId
        ): array
    {
        $qb = $this->createQueryBuilder('e')
            ->Where('e.organisation = :organisationId')
            ->setParameter('organisationId', $organisationId);

        if (!empty($date)) {
            $selectedDate = new \DateTime($date);
            $qb->andWhere('e.startDate >= :date')
            ->setParameter('date', $selectedDate);
        }
    
       // return $this->createQueryBuilder('e')
            // ->where('e.startDate >= :date')
            // ->andWhere('e.organisation = :organisationId')
            // ->setParameter('date', $selectedDate)
            // ->setParameter('organisationId', $organisationId)
        return $qb->orderBy('e.startDate', 'ASC')
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
            ->addSelect('l');

        if (!empty($organisations)) {
            $qb->andWhere('e.organisation IN (:organisations)')
                ->setParameter('organisations', $organisations);
        }

        if (!empty($thematics)) {
            $qb->andWhere('e.thematic IN (:thematics)')
                ->setParameter('thematics', array_map(fn(string $t) => ThematicEnum::from($t), $thematics));
        }

        if (!empty($fees)) {
            $qb->andWhere('e.fee IN (:fees)')
                ->setParameter('fees', array_map(fn(string $f) => FeeEnum::from($f), $fees));
        }

        if (!empty($publics)) {
            $qb->andWhere('e.public IN (:publics)')
                ->setParameter('publics', array_map(fn(string $p) => PublicEnum::from($p), $publics));
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
            $selectedDate = new \DateTimeImmutable($date);
            $limitDate = $selectedDate->modify('+15 days');
            $qb->andWhere('e.startDate BETWEEN :date AND :limitDate')
                ->setParameter('date', $selectedDate)
                ->setParameter('limitDate', $limitDate);
        }

        // if (!empty($date)) {
        //     $selectedDate = new \DateTime($date);
        //     $limitDate = (clone $selectedDate)->add(new \DateInterval('P15D'));
        //     $qb->andWhere('e.startDate >= :date')
        //         ->andWhere('e.startDate <= :limitDate')
        //         ->setParameter('date', $selectedDate)
        //         ->setParameter('limitDate', $limitDate);
        // }

// $sql = $qb->getQuery()->getSQL();
// dump($qb->getParameters());
// dd($sql);

        return $qb->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

}


   