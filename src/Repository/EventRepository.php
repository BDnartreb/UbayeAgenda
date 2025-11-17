<?php

namespace App\Repository;

use App\Entity\Event;
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

    public function findEventsByFiltersOrderedByStartDateFromToday(
        int $organisationId,
        int $thematic,
        int $fee,
        int $public,
        int $town,
        int $location,
        ): array
        {
            
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
