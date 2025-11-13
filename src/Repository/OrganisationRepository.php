<?php

namespace App\Repository;

use App\Entity\Organisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Organisation>
 */
class OrganisationRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organisation::class);
    }

    /**
     * Used to upgrade (rehash) the organisation's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $organisation, string $newHashedPassword): void
    {
        if (!$organisation instanceof Organisation) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $organisation::class));
        }

        $organisation->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($organisation);
        $this->getEntityManager()->flush();
    }

    public function findByRole(string $role): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT id FROM organisation
            WHERE roles::jsonb @> :roleJson
        ';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('roleJson', json_encode([$role]));
        $result = $stmt->executeQuery()->fetchFirstColumn();

        if (empty($result)) {
            return [];
        }

        // Recharge les entités Doctrine correspondantes à ces IDs
        return $this->createQueryBuilder('o')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $result)
            ->getQuery()
            ->getResult();
    }



    //    /**
    //     * @return Organisation[] Returns an array of Organisation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Organisation
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
