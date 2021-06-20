<?php

namespace App\Repository;

use App\Entity\Car;
use App\Entity\Company;
use App\Entity\Reservation;
use App\Entity\User;
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

    /**
     * @param Company $company
     * @param DateTime $start
     * @param DateTime $end
     * @return int[]
     * @throws Exception
     */
    public function getCarsReservedBetweenDates(Company $company, DateTime $start, DateTime $end)
    {
        return $this->createQueryBuilder('reservation')
            ->join('reservation.user', 'u')
            ->join('reservation.car', 'c')
            ->Where('u.company = :val')
            ->setParameter('val', $company)
            ->andWhere('(reservation.start <= :start AND reservation.end >= :end) OR (reservation.start BETWEEN :start AND :end AND reservation.end >= :end) OR (reservation.end BETWEEN :start AND :end AND reservation.start <= :start)')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->select('c.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Company $company
     * @param Car|null $car
     * @param User|null $user
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getCountWithUserAndCar(Company $company, Car $car = null, User $user = null)
    {
        if (empty($user) && empty($car)) {
            return $this->createQueryBuilder('reservation')
                ->join('reservation.user', 'u')
                ->andWhere('u.company = :val')
                ->setParameter('val', $company)
                ->select('count(reservation.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }
        elseif (empty($user)) {
            return $this->createQueryBuilder('reservation')
                ->join('reservation.user', 'u')
                ->andWhere('reservation.car = :car')
                ->setParameter('car', $car)
                ->andWhere('u.company = :val')
                ->setParameter('val', $company)
                ->select('count(reservation.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }
        elseif ( empty($car)) {
            return $this->createQueryBuilder('reservation')
                ->join('reservation.user', 'u')
                ->andWhere('reservation.user = :user')
                ->setParameter('user', $user)
                ->andWhere('u.company = :val')
                ->setParameter('val', $company)
                ->select('count(reservation.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }
        else {
            return $this->createQueryBuilder('reservation')
                ->join('reservation.user', 'u')
                ->andWhere('reservation.car = :car')
                ->setParameter('car', $car)
                ->andWhere('reservation.user = :user')
                ->setParameter('user', $user)
                ->andWhere('u.company = :val')
                ->setParameter('val', $company)
                ->select('count(reservation.id)')
                ->getQuery()
                ->getSingleScalarResult();
        }
    }

    /**
     * @param Company $company
     * @param $per_page
     * @param $current_page
     * @param $order
     * @param $direction
     * @param Car|null $car
     * @param User|null $user
     * @return Reservation[]
     */
    public function reservationPagination(Company $company, $per_page, $current_page, $order, $direction, Car $car = null, User $user = null)
    {
        if (empty($user) && empty($car)) {
            return $this->createQueryBuilder('reservation')
                ->join('reservation.user', 'user')
                ->join('reservation.car', 'car')
                ->andWhere('user.company = :val')
                ->setParameter('val', $company)
                ->orderBy($order, $direction)
                ->setMaxResults($per_page)
                ->setFirstResult(($current_page - 1) * $per_page)
                ->getQuery()
                ->getResult();
        }
        elseif (empty($user)) {
            return $this->createQueryBuilder('reservation')
                ->join('reservation.user', 'user')
                ->join('reservation.car', 'car')
                ->andWhere('reservation.car = :car')
                ->setParameter('car', $car)
                ->andWhere('user.company = :val')
                ->setParameter('val', $company)
                ->orderBy($order, $direction)
                ->setMaxResults($per_page)
                ->setFirstResult(($current_page - 1) * $per_page)
                ->getQuery()
                ->getResult();
        }
        elseif ( empty($car)) {
            return $this->createQueryBuilder('reservation')
                ->join('reservation.user', 'user')
                ->join('reservation.car', 'car')
                ->andWhere('reservation.user = :user')
                ->setParameter('user', $user)
                ->andWhere('user.company = :val')
                ->setParameter('val', $company)
                ->orderBy($order, $direction)
                ->setMaxResults($per_page)
                ->setFirstResult(($current_page - 1) * $per_page)
                ->getQuery()
                ->getResult();
        }
        else {
            return $this->createQueryBuilder('reservation')
                ->join('reservation.user', 'user')
                ->join('reservation.car', 'car')
                ->andWhere('reservation.car = :car')
                ->setParameter('car', $car)
                ->andWhere('reservation.user = :user')
                ->setParameter('user', $user)
                ->andWhere('user.company = :val')
                ->setParameter('val', $company)
                ->orderBy($order, $direction)
                ->setMaxResults($per_page)
                ->setFirstResult(($current_page - 1) * $per_page)
                ->getQuery()
                ->getResult();
        }
    }
}
