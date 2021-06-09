<?php

namespace App\Repository;

use App\Entity\Car;
use App\Entity\Company;
use App\Entity\Reservation;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * @param Company $company
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getCount(Company $company)
    {
        return $this->createQueryBuilder('reservation')
            ->join('reservation.user', 'u')
            ->andWhere('u.company = :val')
            ->setParameter('val', $company)
            ->select('count(reservation.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getCurrentCount(Company $company)
    {
        return $this->createQueryBuilder('reservation')
            ->join('reservation.user', 'u')
            ->andWhere('u.company = :val')
            ->setParameter('val', $company)
            ->andWhere('reservation.start < :start')
            ->setParameter('start', new DateTime('now'))
            ->andWhere('reservation.end > :end')
            ->setParameter('end', new DateTime('now'))
            ->select('count(reservation.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getFutureCount(?Company $company)
    {
        return $this->createQueryBuilder('reservation')
        ->join('reservation.user', 'u')
        ->andWhere('u.company = :val')
        ->setParameter('val', $company)
        ->andWhere('reservation.start > :start')
        ->setParameter('start', new DateTime('now'))
        ->select('count(reservation.id)')
        ->getQuery()
        ->getSingleScalarResult();
    }

    /**
     * @param Company $company
     * @return int[]
     * @throws Exception
     */
    public function getCurrentCars(Company $company)
    {
        return $this->createQueryBuilder('reservation')
            ->join('reservation.user', 'u')
            ->join('reservation.car', 'c')
            ->andWhere('u.company = :val')
            ->setParameter('val', $company)
            ->andWhere('reservation.start < :start')
            ->setParameter('start', new DateTime('now'))
            ->andWhere('reservation.end > :end')
            ->setParameter('end', new DateTime('now'))
            ->select('c.id')
            ->getQuery()
            ->getResult();
    }
}
